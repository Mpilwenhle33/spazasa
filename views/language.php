<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Language</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div id="page-language" class="page-section active">
    <div class="lang-page">
        <div class="lang-hero-strip"></div>
        <div class="lang-content">
            <div class="lang-left">
                <h2 class="lang-heading">Please select desirable <span>language</span></h2>
                <button class="lang-btn" onclick="setLanguage('isizulu')">ISIZULU</button>
                <button class="lang-btn" onclick="setLanguage('sesotho')">SESOTHO</button>
                <button class="lang-btn" onclick="setLanguage('english')">ENGLISH</button>
                <button class="lang-btn" onclick="setLanguage('afrikaans')">AFRIKAANS</button>
            </div>
            <div class="lang-img">
                <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=600" alt="Happy shopper" />
            </div>
        </div>
    </div>
</div>

<script>
function setLanguage(lang) {
    fetch('index.php?action=set-language', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: lang })
    }).then(() => {
        showToast('Language selected! 🎉');
        setTimeout(() => {
            window.location = 'index.php?action=marketplace';
        }, 1200);
    });
}

function showToast(msg) {
    let t = document.getElementById('toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast';
        t.className = 'toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 1200);
}
</script>

<script src="assets/js/app.js"></script>
</body>
</html>
