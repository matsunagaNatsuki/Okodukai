import $ from 'jquery';
// ajax.jsのメソッドを読み込む
import { ajaxError, configureAjax, handleAuthenticationError } from './modules/ajax';
// import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// jQueryを他のJavascriptファイルからも使用できる
window.$ = window.jQuery = $;

const $document = $(document);

// ajax.jsのメソッド
configureAjax();

// 読み込み中のローディングの表示
function showLoading(message = '読み込み中...') {
    const $overlay = $('#loading-overlay');
    $overlay.find('.loading-text').text(message);
    $overlay.attr('aria-hidden', 'false').addClass('is-visible');
}

// 読み込み中のローディングを閉じる処理
function hideLoading() {
    $('#loading-overlay').attr('aria-hidden', 'true').removeClass('is-visible');
}

// お手伝い設定と家族アカウントのデータ削除のモーダル処理
function openDeleteModal($trigger) {
    const $modal = $('#common-modal');

    // 削除選択した家族アカウントid
    $modal.data('delete-url', $trigger.data('delete-url'));
    // 削除選択した家族アカウントのデータ
    $modal.data('delete-target', $trigger.closest($trigger.data('delete-target')));
    // 削除モーダルのタイトル名とcssクラスを取得
    $modal.find('.app-modal-title').text($trigger.data('delete-title') || '削除の確認');
    // 削除モーダルのメッセージとcssクラスの取得
    $modal.find('.app-modal-body').text($trigger.data('delete-message') || 'このデータを削除しますか？');
    // 削除モーダルのボタンのクラス名の追加と表示名を「削除する」に変更
    $modal.find('[data-modal-confirm]').text('削除する').addClass('button--danger');
    // 削除モーダルのボタンのクラス名の追加とaria-hiddenをfalseに変更
    $modal.attr('aria-hidden', 'false').addClass('is-visible');
    // 削除モーダルを表示
    $('body').addClass('is-modal-open');
    // 要素をフォーカスさせる
    $modal.find('[data-modal-confirm]').trigger('focus');
}

// 削除モーダルを閉じる処理
function closeModal() {
    const $modal = $('#common-modal');
    // aria-hiddenをfalseに変更とcssクラスの追加
    $modal.attr('aria-hidden', 'true').removeClass('is-visible');
    // 削除モーダルの実行ボタンのクラス名の変更と操作の無効化を解除する
    $modal.find('[data-modal-confirm]').removeClass('button--danger').prop('disabled', false);
    // cssクラスを削除してモーダルを閉じる
    $('body').removeClass('is-modal-open');
}

// おこづかい設定と家族アカウント関係のflash-message処理
function showAjaxMessage(message, type = 'success') {
    const $message = $('[data-ajax-message]');

    // flashメッセージがなかったら処理を終了
    if (!$message.length) {
        return;
    }

// 削除jsonメッセージを表示
    const $text = $('<span>').text(message);
    // flashメッセージのcloseボタン
    const $closeButton = $('<button>', {
        class: 'alert-close',
        type: 'button',
        'aria-label': 'メッセージを閉じる',
    }).text('×');

    $message
        // flashメッセージのクラス名の削除
        .removeClass('app-alert--success app-alert--error')
        // flashメッセージのクラス名の追加
        .addClass(`app-alert app-alert--${type}`)
        // typeがerrorか確認
        .attr('role', type === 'error' ? 'alert' : 'status')
        // データを削除
        .empty()
        // 削除jsonメッセージとcloseボタンを追加
        .append($text, $closeButton)
        // flashメッセージの表示
        .show();
}

// localStorage用にお子様ログイン情報を定義
const childLoginStorageKeys = {
    // 家族コード
    familyCode: 'okozukai_child_family_code',
    // お子様のログインID
    loginId: 'okozukai_child_login_id',
};

// 保存済みの子どものログイン情報を復元する
function restoreChildLogin() {
    // お子様ログインフォームのデータを取得
    const $form = $('[data-child-login-form]');

    // ログインフォームに値が入っていなかったら処理終了
    if (!$form.length) {
        return;
    }

    try {
        // localStorageに保存されている家族コードのフォームの値を取得
        const familyCode = localStorage.getItem(childLoginStorageKeys.familyCode);
        // localStorageに保存されているお子様ログインIDのフォームの値を取得
        const loginId = localStorage.getItem(childLoginStorageKeys.loginId);

        // 家族コードかお子様ログインIDがlocalStorageに保存されていたら
        if (familyCode !== null || loginId !== null) {
            // 家族コードとお子様ログインIDを保存するチェックボックスをオンにする
            $form.find('[data-save-child-login]').prop('checked', true);

            // 入力欄に値があれば優先し、空の場合はlocalStorageに保存された家族コードを設定
            $form.find('[name="family_code"]').val(function (_, currentValue) {
                return currentValue || familyCode || '';
            });

            // 入力欄に値があれば優先し、空の場合はlocalStorageに保存されたログインIDを設定
            $form.find('[name="login_id"]').val(function (_, currentValue) {
                return currentValue || loginId || '';
            });
        }
    } catch {
        // ブラウザ設定でlocalStorageが利用できない場合もログイン自体は継続する。
    }
}

// お子様ログイン情報の保存処理
function saveChildLogin($form) {
    try {
        // お子様ログイン情報を保存にチェックを入れていたら
        if ($form.find('[data-save-child-login]').is(':checked')) {
            // 入力されている家族コードの値をlocalStorageに保存
            localStorage.setItem(childLoginStorageKeys.familyCode, $form.find('[name="family_code"]').val());

            // 入力されているお子様ログインIDの値をlocalStorageに保存
            localStorage.setItem(childLoginStorageKeys.loginId, $form.find('[name="login_id"]').val());
        // お子様ログイン情報を保存にチェックが入っていなかったら
        } else {
            // localStorageに保存している家族コードのデータを削除
            localStorage.removeItem(childLoginStorageKeys.familyCode);
            // localStorageに保存しているログインIDのデータを削除
            localStorage.removeItem(childLoginStorageKeys.loginId);
        }
    } catch {
        // 保存できなくてもサーバーへのログイン処理は止めない。
    }
}

window.Okodukai = { showLoading, hideLoading, closeModal };

// ハンバーガーボタンの実装
$document.on('click', '.nav-toggle', function () {
    // aria-expandedがtrueと一致しているか？
    const isOpen = $(this).attr('aria-expanded') === 'true';
    // ナビゲーションメニューの開閉を反転させる
    $(this).attr('aria-expanded', String(!isOpen));
    // ナビゲーションバーが開いている時は、'is-open'を削除。閉じているときは追加する
    $('#global-navigation').toggleClass('is-open', !isOpen);
});

// flash-messageの閉じるボタンの実装
$document.on('click', '.alert-close', function () {
    const $alert = $(this).closest('.app-alert');

    // アラートを2秒かけて非表示
    $alert.fadeOut(180, function () {
        // おこづかい設定と家族アカウント関係のflash-messageだった時
        if ($alert.is('[data-ajax-message]')) {
            $alert
                .empty() // 中身を削除
                .removeClass(
                    // CSS属性を削除
                    'app-alert app-alert--success app-alert--error'
                )
                // role属性を削除
                .removeAttr('role');

            return;
        }

        // 要素そのものを削除
        $alert.remove();
    });
});

// パスワードの中身を表示
$document.on('click', '[data-password-toggle]', function () {
    // data-password-toggleをjQueryオブジェクトに変換
    const $button = $(this);
    // data-password-toggleの値を取得
    const $input = $($button.data('password-toggle'));
    // type = passwordか確認
    const shouldShow = $input.attr('type') === 'password';

    // typeがpasswordか確認し、trueなら'text'でパスワードの値を表示
    // falseなら'password'で値を隠す
    $input.attr('type', shouldShow ? 'text' : 'password');
    // パスワード表示ボタンのtypeがpasswordか確認し、trueなら「非表示」、falseなら「表示」でボタンの文字を変更
    $button.text(shouldShow ? '非表示' : '表示');
    // HTMLのaria-labelの値をtypeがpasswordか確認し、trueなら「パスワードを非表示にする」falseなら「パスワードを表示する」に変更
    $button.attr('aria-label', shouldShow ? 'パスワードを非表示にする' : 'パスワードを表示する');
});

// お手伝いの金額の表示
$document.on('change', '[data-chore-select]', function () {
    // optionタグにて選択したお手伝い設定の金額の値を取得
    const rewardAmount = $(this).find(':selected').data('reward-amount');
    // data-chore-reward属性を持つ要素を取得し、設定金額を表示。設定金額がなければから文字で返す。
    $(this).closest('form').find('[data-chore-reward]').val(rewardAmount ?? '');
});

// プロフィール画像のプレビュー
$document.on('change', '[data-profile-image-input]', function () {
    // 選択したプロフィール画像のファイルを取得
    const file = this.files?.[0];

    // ファイルが選択されていない or 選択した画像ファイルがimage/から始まっていない
    if (!file || !file.type.startsWith('image/')) {
        // プロフィール画像の処理を中断
        return;
    }

    // 選択したファイルの一時保存URLを作成
    const previewUrl = URL.createObjectURL(file);
    const $preview = $('[data-profile-preview]');
    // const previousUrl = $preview.data('preview-url');

    // if (previousUrl) {
    //     URL.revokeObjectURL(previousUrl);
    // }

    // $preview.attr('src', previewUrl).data('preview-url', previewUrl);

    // src属性をプレビュー用URLに変更
    $preview.attr('src', previewUrl);
});

// $document.on('click', '[data-modal-open]', function () {
//     const $trigger = $(this);
//     const $modal = $('#common-modal');

//     $modal.find('.app-modal-title').text($trigger.data('modal-title') || '確認');
//     $modal.find('.app-modal-body').text($trigger.data('modal-message') || '操作を続けますか？');
//     $modal.find('[data-modal-confirm]').text($trigger.data('modal-confirm-label') || '続ける');
//     $modal.attr('aria-hidden', 'false').addClass('is-visible');
//     $('body').addClass('is-modal-open');
//     $modal.find('[data-modal-confirm]').trigger('focus');
// });

// 削除モーダルのキャンセルと×ボタンでモーダルを閉じる処理
$document.on('click', '[data-modal-close]', closeModal);

// お手伝い設定の編集フォームの表示
$document.on('click', '[data-chore-edit]', function () {
    // data-chore-cardの要素を受け取る
    const $card = $(this).closest('[data-chore-card]');
    // 編集・削除ボタンをhiddenで非表示
    $card.find('[data-chore-display]').attr('hidden', true);
    // お手伝い編集フォームのhidden要素を削除し、編集フォームを表示させる
    $card.find('[data-chore-edit-form]').removeAttr('hidden');
    // お手伝い名の入力欄にカーソルが移動
    $card.find('[name="chore_name"]').trigger('focus');
});

// お手伝い編集のキャンセル
$document.on('click', '[data-chore-cancel]', function () {
    // data-chore-cardの要素を受け取る
    const $card = $(this).closest('[data-chore-card]');
    // data-edit-errorsの要素の中身を空にする
    $card.find('[data-edit-errors]').empty();
    // 編集フォームをhiddenにより非表示にする
    $card.find('[data-chore-edit-form]').attr('hidden', true);
    // 編集・削除ボタンを表示させる
    $card.find('[data-chore-display]').removeAttr('hidden');
});

// お手伝い編集フォームの保存処理
$document.on('submit', '[data-chore-edit-form]', function (event) {
    // サーバー側のフォーム送信を停止し、代わりにAjaxでフォームを送信
    event.preventDefault();

    const $form = $(this);
    // data-chore-cardの要素を受け取る
    const $card = $form.closest('[data-chore-card]');
    // フォーム送信の入力要素を取得
    const $submit = $form.find(':submit');

    // 編集フォームの保存処理を行なっている間はフォームの入力を無効化する
    $submit.prop('disabled', true);

    // data-edit-errors属性の要素の中身を削除
    $form.find('[data-edit-errors]').empty();
    // 読み込み中のローディングの表示
    showLoading('更新しています...');

    $.ajax({
        // formタグにあるaction属性を取得
        url: $form.attr('action'),
        // method属性を取得
        method: 'PUT',
        // フォームの入力値をURLエンコード形式の文字列に変換
        data: $form.serialize(),

    // Ajax通信が成功したら
    }).done(function (response) {
        // コントローラから返された更新後のお手伝い名を画面に反映する
        $card.find('[data-chore-name]').text(response.chore.chore_name);
        // コントローラから返された更新後のおこづかい金額を画面に反映する
        $card.find('[data-chore-reward]').text(response.chore.reward_amount_label);
        // お手伝い名編集後の削除モーダルのメッセージの表記
        $card.find('[data-ajax-delete]').attr('data-delete-message', `「${response.chore.chore_name}」を削除しますか？`);
        // 編集フォームをhiddenにより非表示にする
        $form.attr('hidden', true);
        // 編集・削除ボタンを表示させる
        $card.find('[data-chore-display]').removeAttr('hidden');
        // flash-message処理
        showAjaxMessage(response.message);

    // Ajax通信が失敗したら
    }).fail(function (xhr) {
        // Ajax通信でエラーが発生した時にエラー内容を返す
        const error = ajaxError(xhr);
        // Ajaxエラーメッセージを表示
        $form.find('[data-edit-errors]').text(error.message);
        // Ajaxの再読み込み処理
        handleAuthenticationError(xhr);

    // 成功、失敗共通で
    }).always(function () {
        // フォーム入力の有効化
        $submit.prop('disabled', false);
        // 読み込み中のローディング処理を閉じる
        hideLoading();
    });
});

// 家族アカウントとお手伝い設定の削除
$document.on('click', '[data-ajax-delete]', function () {
    // データ削除のモーダル処理
    openDeleteModal($(this));
});

// 削除モーダルの実行ボタンの処理
$document.on('click', '#common-modal [data-modal-confirm]', function () {
    const $modal = $('#common-modal');
    // 削除選択した家族アカウントid
    const deleteUrl = $modal.data('delete-url');

    // 家族アカウントがない時は処理を止める
    if (!deleteUrl) {
        return;
    }

    // 削除選択した家族アカウントのデータを取得
    const $target = $modal.data('delete-target');
    // 削除ボタンの連打による、削除リクエストの重複送信を防ぐ
    const $confirm = $(this).prop('disabled', true);
    // 削除モーダルを閉じる処理
    closeModal();
    // 削除処理のローディングの処理
    showLoading('削除しています...');

    // サーバーで削除処理
    $.ajax({
        url: deleteUrl,
        method: 'DELETE',

        // 削除成功
    }).done(function (response) {
        // 削除したデータを1.8秒かけて非表示
        $target.fadeOut(180, function () {
            // データ削除
            $(this).remove();
        });

        // flashメッセージ処理
        showAjaxMessage(response.message);

        // 削除が失敗の時
    }).fail(function (xhr) {
        // Ajaxエラー処理
        const error = ajaxError(xhr);
        // flashメッセージのエラーメッセージ
        showAjaxMessage(error.message, 'error');
        // 再読み込みの処理
        handleAuthenticationError(xhr);

        // 削除の成功・失敗にも関わらず
    }).always(function () {
        // 削除実行ボタンの有効化に変更
        $confirm.prop('disabled', false);
        // 選択したデータを削除
        $modal.removeData('delete-url delete-target');
        // 読み込み中のローディングを閉じる処理
        hideLoading();
    });
});

// Escキーを押すとモーダルを閉じる処理
$document.on('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// お手伝い実績の削除確認ダイヤログを表示
$document.on('submit', 'form[data-loading]', function () {
    const $form = $(this);

    // formタブにあるdata-confirm-submit属性があり、確認済みでない場合
    if ($form.is('[data-confirm-submit]') && !$form.data('confirmed')) {
        // 確認ダイヤログをキャンセルに選択した場合
        if (!window.confirm($form.data('confirm-submit'))) {
            // フォーム送信を中断
            return false;
        }

        // 確認済みとして保存
        $form.data('confirmed', true);
    }

    // フォーム送信中か確認
    if ($form.data('submitting')) {
        // 送信中の場合は処理を中断
        return false;
    }

    // お子様ログインフォームの属性なら
    if ($form.is('[data-child-login-form]')) {
        // お子様ログイン情報の保存処理を行う
        saveChildLogin($form);
    }

    // 送信中の状態に設定
    $form.data('submitting', true);
    // フォーム内の送信ボタンを取得して、クリックできない状態にする
    $form.find(':submit').prop('disabled', true);
    // 読み込み中のローディングの表示
    showLoading($form.data('loading-message') || '送信中...');
});

// ページがリロードされて画面が表示されたときに、読み込み中のローディングを閉じる
$(window).on('pageshow', hideLoading);

// バリデーションエラーの一覧をfocus
$(function () {
    $('.validation-errors').trigger('focus');
    restoreChildLogin();
});
