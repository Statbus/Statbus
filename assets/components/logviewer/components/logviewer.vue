<template>
  <div>
    <p v-if="loading">Loading {{ url }} ({{ progress }}%)…</p>

    <div v-else>
      <p class="px-2">
        Showing lines {{ startLine + 1 }}–{{ endLine }} of {{ filteredLines.length }}
      </p>

      <div class="d-flex justify-content-between border-bottom border-4">
        <div class="btn-group mr-2" role="group" aria-label="Log Pages">
          <button class="btn btn-outline-primary" @click="prevPage" :disabled="page === 0">Prev</button>
          <button class="btn btn-outline-primary" @click="nextPage" :disabled="endLine >= filteredLines.length">
            Next
          </button>
        </div>

        <input v-model="filterText" placeholder="Filter (substring or /regex/)" class="filter form-control" />
      </div>

      <div class="table-responsive">
        <table class="table table-sm font-monospace" style="font-size:.85rem;">
          <tbody>
            <log-line v-for="line in visibleLines" :key="line.lineNumber" :line="line" />
          </tbody>
        </table>
      </div>

      <div class="controls">
        <button @click="prevPage" :disabled="page === 0">Previous</button>
        <button @click="nextPage" :disabled="endLine >= filteredLines.length">Next</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import logLine from "./logLine.vue";

const props = defineProps({
  url: { type: String, required: true }
});

const lines = ref([]);
const loading = ref(true);
const progress = ref(0);
const page = ref(0);
const filterText = ref('');
const pageSize = 200;

const tsRegex = /^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)\]/;
const areaRegex = /\(([^()]+) \(([\d,]+)\)\)$/;

function parseLine(text, index) {
  const tsMatch = text.match(tsRegex);
  const ts = tsMatch ? tsMatch[1] : null;

  const areaMatch = text.match(areaRegex);
  const location = areaMatch
    ? { area: areaMatch[1], coords: areaMatch[2] }
    : null;

  const cleaned = text.replace(tsRegex, '').replace(areaRegex, '').trim();
  return { lineNumber: index, text: cleaned, ts, location };
}

function updateHash(anchor = null) {
  const params = new URLSearchParams();
  if (filterText.value) params.set('filter', filterText.value);
  if (page.value > 0) params.set('page', page.value);

  let hash = anchor ? anchor : '';
  const paramString = params.toString();
  if (paramString) hash += '?' + paramString;

  history.replaceState(null, '', hash ? '#' + hash : window.location.pathname);
}

// Restore hash state
onMounted(() => {
  const hash = window.location.hash.slice(1);
  if (!hash) return;

  const [anchorPart, queryPart] = hash.split('?');
  const anchor = anchorPart || null;

  if (queryPart) {
    const params = new URLSearchParams(queryPart);
    filterText.value = params.get('filter') || '';
    page.value = parseInt(params.get('page')) || 0;
  }

  if (anchor) {
    const el = document.getElementById(anchor);
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
});

// Keep hash in sync (debounced)
let hashTimeout;
watch([filterText, page], () => {
  clearTimeout(hashTimeout);
  hashTimeout = setTimeout(updateHash, 400);
});

// Filtering + paging
const filteredLines = computed(() => {
  const term = filterText.value.trim();
  if (!term) return lines.value;

  try {
    if (term.startsWith('/') && term.endsWith('/')) {
      const r = new RegExp(term.slice(1, -1), 'i');
      return lines.value.filter(l => r.test(l.text));
    }
    return lines.value.filter(l => l.text.toLowerCase().includes(term.toLowerCase()));
  } catch {
    return lines.value;
  }
});

const startLine = computed(() => page.value * pageSize);
const endLine = computed(() => Math.min(startLine.value + pageSize, filteredLines.value.length));
const visibleLines = computed(() => filteredLines.value.slice(startLine.value, endLine.value));

const nextPage = () => (page.value += 1);
const prevPage = () => (page.value -= 1);

// Load log file (streaming if possible)
onMounted(async () => {
  const res = await fetch(props.url);
  const reader = res.body?.getReader();
  const decoder = new TextDecoder();
  let partial = '';
  let count = 0;
  let received = 0;
  const contentLength = +res.headers.get('Content-Length') || 0;

  if (!reader) {
    // Non-streaming fallback
    lines.value = (await res.text()).split('\n').map((t, i) => parseLine(t, i + 1));
    loading.value = false;
    return;
  }

  while (true) {
    const { value, done } = await reader.read();
    if (done) break;

    partial += decoder.decode(value, { stream: true });
    const parts = partial.split('\n');
    partial = parts.pop();

    for (const text of parts) {
      count++;
      lines.value.push(parseLine(text, count));
    }

    received += value.length;
    if (contentLength) progress.value = Math.round((received / contentLength) * 100);
  }

  if (partial) {
    count++;
    lines.value.push(parseLine(partial, count));
  }

  loading.value = false;
});
</script>
