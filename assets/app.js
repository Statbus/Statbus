import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

import "./styles/app.scss";

import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";

tippy("[title]", {
  content(reference) {
    const title = reference.getAttribute("title");
    reference.removeAttribute("title");
    return title;
  },
});

tippy("[data-url]", {
  appendTo: () => document.body,
  allowHTML: true,
  interactive: true,
  content: "Loading...",
  onCreate(instance) {
    instance._isFetching = false;
    instance._src = null;
    instance._error = null;
  },
  onShow(instance) {
    if (instance._isFetching || instance._src || instance._error) {
      return;
    }
    instance._isFetching = true;
    fetch(instance.reference.dataset.url)
      .then((response) => response.text())
      .then((html) => {
        let parser = new DOMParser();
        let data = parser.parseFromString(html, "text/html");
        instance.setContent(data.body.outerHTML);
      })
      .catch((error) => {
        instance._error = error;
        instance.setContent(`Request failed. ${error}`);
      })
      .finally(() => {
        instance._isFetching = false;
      });
  },
  content(reference) {
    const title = reference.dataset.url;
    return title;
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
