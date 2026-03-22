/* カスタマイズ用JavaScript */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.searchform'); // フォームのセレクターを適宜調整してください
    form.addEventListener('submit', function(e) {
        // Web検索が選択されているか確認
        if (document.getElementById('search_google').checked) {
            e.preventDefault(); // フォームの送信を停止
            const searchQuery = document.querySelector('.search-name').value;
            const fixedKeyword = 'ライオンエキスプレス'; // 固定キーワード
            const googleSearchUrl = `https://www.google.com/search?q=${encodeURIComponent(searchQuery + ' ' + fixedKeyword)}`;
            window.open(googleSearchUrl, '_blank'); // 新しいタブでGoogle検索ページにリダイレクト
        }
        // サイト内検索の場合はフォームが通常通り送信されます
    });
});