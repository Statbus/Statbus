const autoComplete = require("@tarekraafat/autocomplete.js");

const searchForm = document.getElementById("globalSearchForm");
const globalSearchEl = document.getElementById("globalSearch");
const ignoreInputFocusEls = ["input", "textarea"];
const nav = document.getElementById("primaryNav");

const hasGlobalSearch = null !== globalSearchEl;

function toTitleCase(str) {
  return str.replace(/\w\S*/g, function (txt) {
    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
  });
}

document.addEventListener("keydown", (e) => {
  if ("Slash" == e.code) {
    if (
      !ignoreInputFocusEls.includes(
        document.activeElement.tagName.toLowerCase()
      )
    ) {
      e.preventDefault();
      searchForm.classList.toggle("w-100");
    }
    globalSearchEl.focus();
  }
});

if (hasGlobalSearch) {
  globalSearchEl.addEventListener("focus", (e) => {
    searchForm.classList.add("w-100");
    nav.classList.add("visually-hidden");
  });
  globalSearchEl.addEventListener("blur", (e) => {
    searchForm.classList.remove("w-100");
    nav.classList.remove("visually-hidden");
  });

  const searchUrl = searchForm.getAttribute("action");
  const searchDest = searchForm.dataset.searchdest;

  const autoCompleteConfig = {
    selector: "#globalSearch",
    data: {
      src: async () => {
        const source = await fetch(searchUrl, {
          method: "post",
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            term: document.querySelector("#globalSearch").value,
          }),
        });
        const data = await source.json();
        return data.results;
      },
      keys: ["ckey", "round", "station_name"],
    },
    resultItem: {
      element: (item, data) => {
        switch (data.key) {
          case "ckey":
            var icon = '<i class="fa-solid fa-user"></i>';
            break;
          case "round":
            var icon = '<i class="fa-solid fa-circle"></i>';
            break;
          case "station_name":
            var icon = '<i class="fa-solid fa-satellite"></i>';
            break;
        }
        item.innerHTML = `
            <span>
              ${data.match}
            </span>
            <span>
              ${icon} ${toTitleCase(data.key.replace("_", " "))}
            </span>`;
      },
      tag: "li",
      class:
        "list-group-item d-flex justify-content-between align-items-center",
      highlight: "autoComplete_highlight",
      selected: "autoComplete_selected active",
    },
    debounce: 300,
    threshold: 3,
    highlight: true,
    resultsList: {
      tabSelect: true,
      tag: "ul",
      id: "globalSearchResults",
      class: "list-group",
      maxResults: undefined,
      noResults: true,
    },
    events: {
      input: {
        focus() {
          document.getElementById("globalSearchForm");
          const inputValue = autoCompleteJS.input.value;
          if (inputValue.length) autoCompleteJS.start();
        },
      },
    },
  };

  const autoCompleteJS = new autoComplete(autoCompleteConfig);
  document
    .getElementById("globalSearch")
    .addEventListener("selection", function (event) {
      if ("ckey" == event.detail.selection.key) {
        window.location = `/player/${event.detail.selection.value.ckey}`;
      } else {
        window.location = `/rounds/${event.detail.selection.value.round}`;
      }
    });
}
