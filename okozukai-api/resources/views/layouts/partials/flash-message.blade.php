@foreach (['success', 'error', 'warning', 'info'] as $type)
    @if (session()->has($type))
        <div class="app-alert app-alert--{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}">
            <span>{{ session($type) }}</span>
            <button class="alert-close" type="button" aria-label="メッセージを閉じる">×</button>
        </div>
    @endif
@endforeach

@if (session()->has('status'))
    <div class="app-alert app-alert--success" role="status">
        <span>{{ __(session('status')) }}</span>
        <button class="alert-close" type="button" aria-label="メッセージを閉じる">×</button>
    </div>
@endif
