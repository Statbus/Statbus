// const { assign } = require("core-js/core/object");

const mob = document.querySelector("#mugshot");
const stationId = document.querySelector("#idcard");
const corp = document.querySelector("#corp");
const form = document.querySelector("#generator");

const humanSkintones = [
  "#ffe0d1",
  "#fcccb3",
  "#e8b59b",
  "#d9ae96",
  "#c79b8b",
  "#ffdeb3",
  "#e3ba84",
  "#c4915e",
  "#b87840",
  "#754523",
  "#471c18",
  "#fff4e6",
  "#ffc905",
];

form.addEventListener("submit", async function (e) {
  e.preventDefault();
  const data = new FormData(form);
  let action = form.getAttribute("action");
  if (
    e.submitter != undefined &&
    "badger_assignBtn" === e.submitter.getAttribute("id")
  ) {
    action = document
      .querySelector("#badger_assignBtn")
      .getAttribute("formaction");
  }
  const response = await fetch(action, {
    method: "POST",
    body: data,
  });
  const json = await response.json();
  stationId.setAttribute(
    "src",
    `data:image/png;base64,${json.output.stationId}`
  );
  corp.setAttribute("src", `data:image/png;base64,${json.output.corpId}`);
  mob.setAttribute("src", `data:image/png;base64,${json.output.mob}`);
  updateFields(json.output.request);
});
form.addEventListener("change", (e) => {
  form.requestSubmit();
});

//Time for HORRIBLE HACKS
const HUMAN_SPECIES = "App\\Entity\\Badger\\Species\\Human";

const species = document.querySelector("#badger_species");
const skintone = document.querySelector("#badger_skinTone");
const humanSkintone = document.querySelector("#badger_humanSkinTone");

function updateSkintoneVisibility() {
  const isHuman = species.value === HUMAN_SPECIES;
  skintone.classList.toggle("visually-hidden", isHuman);
  humanSkintone.classList.toggle("visually-hidden", !isHuman);
}

updateSkintoneVisibility();

species.addEventListener("change", updateSkintoneVisibility);

document.querySelectorAll("input.skintone-selector").forEach((input) => {
  const label = document.querySelector(`label[for="${input.id}"]`);
  if (label && input.dataset.color) {
    label.style.background = input.dataset.color;
  }

  input.addEventListener("click", (e) => {
    skintone.value = e.target.value;
  });
});

const assignBtn = document.querySelector("#badger_assignBtn");
const assignSelector = document.querySelector("#badger_assign");

assignSelector.addEventListener("change", (e) => {
  assignBtn.classList.toggle("disabled", e.target.value === "");
});

function updateFields(json) {
  const currentSpecies = json.species.name;
  document.querySelectorAll("[data-for-species]").forEach((FS) => {
    const species = FS.dataset.forSpecies;
    FS.classList.toggle("visually-hidden", currentSpecies != species);
  });
}
