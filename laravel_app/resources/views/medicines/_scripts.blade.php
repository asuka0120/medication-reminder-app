{{--
    お薬フォーム（新規登録・更新）で共通のJavaScript
    呼び出し側で $timeFieldName（'times' または 'timings'）を渡すこと
--}}
<script>
// 自由な時間を追加する関数
function addCustomTime() {
    const container = document.getElementById('custom-times-container');
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.alignItems = 'center';
    wrapper.style.gap = '10px';
    wrapper.style.marginBottom = '8px';
    wrapper.style.padding = '10px';
    wrapper.style.background = '#fff';
    wrapper.style.border = '1px solid #ddd';
    wrapper.style.borderRadius = '8px';

    wrapper.innerHTML = `
        <input type="time" name="{{ $timeFieldName }}[]" class="input-field" style="width: 140px; padding: 5px;">
        <span style="font-size: 0.8em; color: #666;">（自由指定）</span>
        <button type="button" onclick="this.parentElement.remove()" style="color: #f44336; border: none; background: none; cursor: pointer; font-weight: bold; font-size: 1.2em; margin-left: auto;">✕</button>
    `;
    container.appendChild(wrapper);
}

function toggleDosageInput() {
    const select = document.getElementById('dosage_select');
    const manualGroup = document.getElementById('dosage_manual_group');
    const manualInput = document.getElementById('dosage_manual');

    if (select.value === 'other') {
        manualGroup.style.display = 'block';
        manualInput.focus();
    } else {
        manualGroup.style.display = 'none';
    }
}

// 送信前のバリデーション：服用時間が一つも選ばれていなければ送信を中止する
function checkTimes() {
    const checkboxes = document.querySelectorAll('input[name="{{ $timeFieldName }}[]"]:checked');
    const customInputs = document.querySelectorAll('input[type="time"][name="{{ $timeFieldName }}[]"]');

    let hasTime = checkboxes.length > 0;

    customInputs.forEach(input => {
        if (input.value !== "") {
            hasTime = true;
        }
    });

    if (!hasTime) {
        alert("服用時間を設定してください。\n朝・昼・晩などを選ぶか、「時間を追加」から直接入力してください。");
        return false;
    }
    return true;
}
</script>
