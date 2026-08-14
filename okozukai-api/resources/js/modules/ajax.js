import $ from 'jquery';

export function configureAjax() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

export function ajaxError(xhr) {
    const validationErrors = xhr.responseJSON?.errors;

    if (xhr.status === 422 && validationErrors) {
        return {
            message: Object.values(validationErrors).flat().join(' '),
            errors: validationErrors,
        };
    }

    const defaultMessages = {
        0: 'サーバーに接続できません。通信環境をご確認ください。',
        401: 'ログインの有効期限が切れました。もう一度ログインしてください。',
        403: 'この操作を行う権限がありません。',
        404: '対象のデータが見つかりません。画面を再読み込みしてください。',
        419: '画面の有効期限が切れました。再読み込みしてもう一度お試しください。',
        500: 'サーバーで問題が発生しました。時間をおいて再度お試しください。',
    };

    return {
        message: defaultMessages[xhr.status]
            || xhr.responseJSON?.message
            || '通信に失敗しました。時間をおいて再度お試しください。',
        errors: null,
    };
}

export function handleAuthenticationError(xhr) {
    if (xhr.status !== 401) {
        return false;
    }

    window.setTimeout(() => window.location.reload(), 800);

    return true;
}
