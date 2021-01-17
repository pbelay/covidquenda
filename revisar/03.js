const input = document.querySelector('input');
const log = document.getElementById('valores');

input.addEventListener('input', updateValue);

function updateValue(e) {
  log.textContent = e.srcElement.value;
   
}