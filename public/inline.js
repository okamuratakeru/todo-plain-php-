document.querySelectorAll('.title').forEach(title => {
  title.addEventListener('dblclick', () => {
    const span  = title;
    const li    = span.closest('li');
    const id    = li.dataset.id;
    const input = document.createElement('input');
    input.value = span.textContent;
    span.replaceWith(input);
    input.focus();

    let finished = false;
    const finish = async () => {
      if (finished) return;
      finished = true;
      if (!input.isConnected) return; // すでにDOMから削除されていたら何もしない

      const newTxt = input.value.trim();
      if (!newTxt) { input.replaceWith(span); return; }   // 空ならキャンセル

      // 楽観的 UI：先に画面更新
      span.textContent = newTxt;
      input.replaceWith(span);

      // DB 更新
      await fetch('update.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id=${id}&title=${encodeURIComponent(newTxt)}&token=${CSRF_TOKEN}`
      }).catch(() => location.reload());   // エラー時はリロードで整合
    };

    input.addEventListener('blur', () => finish());
    input.addEventListener('keydown', e => e.key === 'Enter' && finish());
  });
});