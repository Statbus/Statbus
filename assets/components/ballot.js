import Sortable from "sortablejs";
var ballot = document.getElementById("ballot-container");
var sortable = Sortable.create(ballot, {
  ghostClass: "alert-info",
});

const castBtn = document.querySelector("#cast");
castBtn.addEventListener("click", function (e) {
  e.preventDefault();
  let vote = new FormData();
  const roster = ballot.querySelectorAll(".candidate");
  roster.forEach((r) => {
    vote.append("candidateId[]", r.dataset.candidateId);
    vote.append("candidateName[]", r.dataset.candidateName);
    r.classList.add("opacity-50");
    castBtn.classList.add("disabled");
  });
  fetch(`${window.location.pathname}/vote`, {
    body: vote,
    method: "post",
  });
  sortable.destroy();
  console.log(vote);
});
