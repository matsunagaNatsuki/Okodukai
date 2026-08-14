@if ($errors->any())
    <div class="validation-errors" role="alert" tabindex="-1">
        <p class="validation-errors__title">入力内容をご確認ください</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
