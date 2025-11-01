<template v-for="(line, i) in visibleLines" :key="line.lineNumber">
    <tr id="L{{ line.lineNumber }}">
        <td :class="{ 'border-0': line.ts || line.location }">#<a :href="'#L' + line.lineNumber">{{
            line.lineNumber }}</a></td>
        <td :class="{ 'border-0': line.ts || line.location }" v-html="linkify(line.text)"></td>
    </tr>
    <tr v-if="line.ts || line.location" :id="'L' + line.lineNumber + '-meta'" style="font-size: .75rem;">
        <td class="p-0"></td>
        <td class="text-muted p-0">
            <span v-if="line.ts">{{ line.ts }}</span> <template v-if="line.location">at
                <span>{{ line.location.area }} - {{ line.location.coords }}
                </span>
            </template>
        </td>
    </tr>
</template>
<script setup>
const linkify = (text) => {
    return text.replace(/([a-zA-Z0-9@]+)\/\(([^)]+)\)/, (_, ckey, character) =>
        `<a href="/player/${ckey}" data-url="/player/${ckey}/popover" target="_blank">${ckey}</a>/(${character})`);
}
const props = defineProps({
    line: {
        required: true,
        type: Object,
    },
});
</script>