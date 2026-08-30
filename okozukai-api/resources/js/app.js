import $ from 'jquery';
import { ajaxError, configureAjax, handleAuthenticationError } from './modules/ajax';
// import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

window.$ = window.jQuery = $;

const $document = $(document);

configureAjax();

function showLoading(message = '読み込み中...') {
    const $overlay = $('#loading-overlay');
    $overlay.find('.loading-text').text(message);
    $overlay.attr('aria-hidden', 'false').addClass('is-visible');
}

function hideLoading() {
    $('#loading-overlay').attr('aria-hidden', 'true').removeClass('is-visible');
}

function closeModal() {
    const $modal = $('#common-modal');
    $modal.attr('aria-hidden', 'true').removeClass('is-visible');
    $modal.find('[data-modal-confirm]').removeClass('button--danger').prop('disabled', false);
    $('body').removeClass('is-modal-open');
}

function openDeleteModal($trigger) {
    const $modal = $('#common-modal');

    $modal.data('delete-url', $trigger.data('delete-url'));
    $modal.data('delete-target', $trigger.closest($trigger.data('delete-target')));
    $modal.find('.app-modal-title').text($trigger.data('delete-title') || '削除の確認');
    $modal.find('.app-modal-body').text($trigger.data('delete-message') || 'このデータを削除しますか？');
    $modal.find('[data-modal-confirm]').text('削除する').addClass('button--danger');
    $modal.attr('aria-hidden', 'false').addClass('is-visible');
    $('body').addClass('is-modal-open');
    $modal.find('[data-modal-confirm]').trigger('focus');
}

function showAjaxMessage(message, type = 'success') {
    const $message = $('[data-ajax-message]');

    if (!$message.length) {
        return;
    }

    $message
        .removeClass('app-alert--success app-alert--error')
        .addClass(`app-alert app-alert--${type}`)
        .text(message)
        .show();
}

const childLoginStorageKeys = {
    familyCode: 'okozukai_child_family_code',
    loginId: 'okozukai_child_login_id',
};

function restoreChildLogin() {
    const $form = $('[data-child-login-form]');

    if (!$form.length) {
        return;
    }

    try {
        const familyCode = localStorage.getItem(childLoginStorageKeys.familyCode);
        const loginId = localStorage.getItem(childLoginStorageKeys.loginId);

        if (familyCode !== null || loginId !== null) {
            $form.find('[data-save-child-login]').prop('checked', true);
            $form.find('[name="family_code"]').val(function (_, currentValue) {
                return currentValue || familyCode || '';
            });
            $form.find('[name="login_id"]').val(function (_, currentValue) {
                return currentValue || loginId || '';
            });
        }
    } catch {
        // ブラウザ設定でlocalStorageが利用できない場合もログイン自体は継続する。
    }
}

function saveChildLogin($form) {
    try {
        if ($form.find('[data-save-child-login]').is(':checked')) {
            localStorage.setItem(childLoginStorageKeys.familyCode, $form.find('[name="family_code"]').val());
            localStorage.setItem(childLoginStorageKeys.loginId, $form.find('[name="login_id"]').val());
        } else {
            localStorage.removeItem(childLoginStorageKeys.familyCode);
            localStorage.removeItem(childLoginStorageKeys.loginId);
        }
    } catch {
        // 保存できなくてもサーバーへのログイン処理は止めない。
    }
}

window.Okodukai = { showLoading, hideLoading, closeModal };

$document.on('click', '.nav-toggle', function () {
    const isOpen = $(this).attr('aria-expanded') === 'true';
    $(this).attr('aria-expanded', String(!isOpen));
    $('#global-navigation').toggleClass('is-open', !isOpen);
});

$document.on('click', '.alert-close', function () {
    $(this).closest('.app-alert').fadeOut(180, function () {
        $(this).remove();
    });
});

$document.on('click', '[data-password-toggle]', function () {
    const $button = $(this);
    const $input = $($button.data('password-toggle'));
    const shouldShow = $input.attr('type') === 'password';

    $input.attr('type', shouldShow ? 'text' : 'password');
    $button.text(shouldShow ? '非表示' : '表示');
    $button.attr('aria-label', shouldShow ? 'パスワードを非表示にする' : 'パスワードを表示する');
});

$document.on('change', '[data-chore-select]', function () {
    const rewardAmount = $(this).find(':selected').data('reward-amount');
    $(this).closest('form').find('[data-chore-reward]').val(rewardAmount ?? '');
});

$document.on('change', '[data-profile-image-input]', function () {
    const file = this.files?.[0];

    if (!file || !file.type.startsWith('image/')) {
        return;
    }

    const previewUrl = URL.createObjectURL(file);
    const $preview = $('[data-profile-preview]');
    const previousUrl = $preview.data('preview-url');

    if (previousUrl) {
        URL.revokeObjectURL(previousUrl);
    }

    $preview.attr('src', previewUrl).data('preview-url', previewUrl);
});

$document.on('click', '[data-modal-open]', function () {
    const $trigger = $(this);
    const $modal = $('#common-modal');

    $modal.find('.app-modal-title').text($trigger.data('modal-title') || '確認');
    $modal.find('.app-modal-body').text($trigger.data('modal-message') || '操作を続けますか？');
    $modal.find('[data-modal-confirm]').text($trigger.data('modal-confirm-label') || '続ける');
    $modal.attr('aria-hidden', 'false').addClass('is-visible');
    $('body').addClass('is-modal-open');
    $modal.find('[data-modal-confirm]').trigger('focus');
});

$document.on('click', '[data-modal-close]', closeModal);

$document.on('click', '[data-chore-edit]', function () {
    const $card = $(this).closest('[data-chore-card]');
    $card.find('[data-chore-display]').attr('hidden', true);
    $card.find('[data-chore-edit-form]').removeAttr('hidden');
    $card.find('[name="chore_name"]').trigger('focus');
});

$document.on('click', '[data-chore-cancel]', function () {
    const $card = $(this).closest('[data-chore-card]');
    $card.find('[data-edit-errors]').empty();
    $card.find('[data-chore-edit-form]').attr('hidden', true);
    $card.find('[data-chore-display]').removeAttr('hidden');
});

$document.on('submit', '[data-chore-edit-form]', function (event) {
    event.preventDefault();

    const $form = $(this);
    const $card = $form.closest('[data-chore-card]');
    const $submit = $form.find(':submit');

    $submit.prop('disabled', true);
    $form.find('[data-edit-errors]').empty();
    showLoading('更新しています...');

    $.ajax({
        url: $form.attr('action'),
        method: 'PUT',
        data: $form.serialize(),
    }).done(function (response) {
        $card.find('[data-chore-name]').text(response.chore.chore_name);
        $card.find('[data-chore-reward]').text(response.chore.reward_amount_label);
        $card.find('[data-ajax-delete]').attr('data-delete-message', `「${response.chore.chore_name}」を削除しますか？`);
        $form.attr('hidden', true);
        $card.find('[data-chore-display]').removeAttr('hidden');
        showAjaxMessage(response.message);
    }).fail(function (xhr) {
        const error = ajaxError(xhr);
        $form.find('[data-edit-errors]').text(error.message);
        handleAuthenticationError(xhr);
    }).always(function () {
        $submit.prop('disabled', false);
        hideLoading();
    });
});

$document.on('click', '[data-ajax-delete]', function () {
    openDeleteModal($(this));
});

$document.on('click', '#common-modal [data-modal-confirm]', function () {
    const $modal = $('#common-modal');
    const deleteUrl = $modal.data('delete-url');

    if (!deleteUrl) {
        return;
    }

    const $target = $modal.data('delete-target');
    const $confirm = $(this).prop('disabled', true);
    closeModal();
    showLoading('削除しています...');

    $.ajax({
        url: deleteUrl,
        method: 'DELETE',
    }).done(function (response) {
        $target.fadeOut(180, function () {
            $(this).remove();
        });
        showAjaxMessage(response.message);
    }).fail(function (xhr) {
        const error = ajaxError(xhr);
        showAjaxMessage(error.message, 'error');
        handleAuthenticationError(xhr);
    }).always(function () {
        $confirm.prop('disabled', false);
        $modal.removeData('delete-url delete-target');
        hideLoading();
    });
});

$document.on('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

$document.on('submit', 'form[data-loading]', function () {
    const $form = $(this);

    if ($form.is('[data-confirm-submit]') && !$form.data('confirmed')) {
        if (!window.confirm($form.data('confirm-submit'))) {
            return false;
        }

        $form.data('confirmed', true);
    }

    if ($form.data('submitting')) {
        return false;
    }

    if ($form.is('[data-child-login-form]')) {
        saveChildLogin($form);
    }

    $form.data('submitting', true);
    $form.find(':submit').prop('disabled', true);
    showLoading($form.data('loading-message') || '送信中...');
});

$(window).on('pageshow', hideLoading);

$(function () {
    $('.validation-errors').trigger('focus');
    restoreChildLogin();
});
