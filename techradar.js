function clear() {
  document.getElementById("TechRadar").classList.remove("is-open");
  document.getElementById("TechRadar").classList.remove("is-frameworks");
  document.getElementById("TechRadar").classList.remove("is-languages");
  document.getElementById("TechRadar").classList.remove("is-tools");
  document.getElementById("TechRadar").classList.remove("is-platforms");
}
function select(section) {
  if (
    document.getElementById("TechRadar").classList.contains("is-" + section)
  ) {
    document.getElementById("TechRadar").classList.remove("is-open");
    document.getElementById("TechRadar").classList.remove("is-" + section);
  } else {
    clear();
    document.getElementById("TechRadar").classList.add("is-open");
    document.getElementById("TechRadar").classList.add("is-" + section);
  }
}
