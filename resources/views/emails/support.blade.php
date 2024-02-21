<h1>{{ translate('Ticket', 'en') }}</h1>
<p>{{ $content }}</p>
<p><b>{{ translate('Sender', 'en') }}: </b>{{ $sender }}</p>
<p>
	<b>{{ translate('Details', 'en') }}:</b>
	<br>
	@php echo $details; @endphp
</p>
<a class="btn btn-primary btn-md" href="{{ $link }}">{{ translate('See ticket', 'en') }}</a>
