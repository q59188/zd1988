<h1>{{ translate('Message', 'en') }}</h1>
<p>{{ $content }}</p>
<p><b>{{ translate('Sender', 'en') }}:</b>{{ $sender }}</p>
<p><b>{{ translate('Message', 'en') }}:</b>{{ $details }}</p>
<a class="btn btn-primary btn-md" href="{{ $link }}">{{ translate('See Details', 'en') }}</a>
