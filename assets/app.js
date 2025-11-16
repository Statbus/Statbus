import "./bootstrap.js";
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

import "./styles/app.scss";

// import tippy from "tippy.js";
import { delegate } from "tippy.js";
import "tippy.js/dist/tippy.css";

delegate(document.body, {
  target: "[title]",
  content(reference) {
    const title = reference.getAttribute("title");
    reference.removeAttribute("title");
    return title;
  },
});

delegate(document.body, {
  target: "[data-url]",
  appendTo: () => document.body,
  allowHTML: true,
  interactive: true,
  content: "Loading...",
  onCreate(instance) {
    instance._isFetching = false;
  },
  onShow(instance) {
    if (instance._isFetching) return;

    instance._isFetching = true;
    fetch(instance.reference.dataset.url)
      .then((r) => r.text())
      .then((html) => {
        if (html.trim() == "") {
          instance.destroy();
        }
        const doc = new DOMParser().parseFromString(html, "text/html");
        instance.setContent(doc.body.outerHTML);
      })
      .catch((err) => {
        instance.setContent(`Request failed: ${err}`);
      })
      .finally(() => {
        instance._isFetching = false;
      });
  },
});

const expiredNote = document.getElementById("expiredNoteModal");
if (expiredNote !== null) {
  const expiredNoteModalElement = new bootstrap.Modal(expiredNote);
  expiredNoteModalElement.show();
}

document.querySelectorAll("[data-href]").forEach((e) => {
  e.addEventListener("click", () => {
    window.location = e.dataset.href;
  });
});

async function ping() {
  try {
    const response = await fetch("/ping");
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }
    const json = await response.json();
    console.log(json);
  } catch (error) {
    console.log(error);
  }
}

await ping();
