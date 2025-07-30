document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const li = btn.closest('li');
    const id = li.dataset.id;
    document.getElementById('del-id').value = id;
    document.getElementById('confirm-modal').removeAttribute('hidden');
  });
});

document.getElementById('confirm-yes').onclick = () => {
  document.getElementById('del-form').submit();
};
document.getElementById('confirm-no').onclick = () => {
  document.getElementById('confirm-modal').setAttribute('hidden', '');
};
