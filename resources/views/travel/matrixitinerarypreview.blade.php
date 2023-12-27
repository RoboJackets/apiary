@foreach($itinerary['itinerary']['slices'] as $slice)
<div style="margin-bottom: 0.5rem; @if(! $loop->first && $loop->count > 1) margin-top: 1.5rem; @endif">{{ $slice['origin']['city']['name'] }} ({{ $slice['origin']['code'] }}) to {{ $slice['destination']['city']['name'] }} ({{ $slice['destination']['code'] }}) on {{ \Carbon\Carbon::parse($slice['departure'])->format('D, M j') }}</div>
@foreach($slice['segments'] as $segment)
<div style="display: grid; grid-template-columns: 5% 95%">
    <img alt="{{ $segment['carrier']['shortName'] }}" src="https://www.gstatic.com/flights/airline_logos/35px/{{ $segment['carrier']['code'] }}.png" style="align-self: center;">
    <div style="display: grid; grid-template-columns: 50% 25% 25%;">
        <div style="display: block;"><strong>{{ $segment['origin']['city']['name'] }} ({{$segment['origin']['code']}}) to {{ $segment['destination']['city']['name'] }} ({{ $segment['destination']['code'] }})</strong> on {{ \Carbon\Carbon::parse($segment['departure'])->format('D, M j') }}</div>
        <div style="display: block; grid-column: 2/span 2;"><strong>{{ \Carbon\Carbon::parse($segment['departure'])->format('g:i A') }}</strong> to <strong>{{ \Carbon\Carbon::parse($segment['arrival'])->format('g:i A') }}</strong> ({{ \Carbon\Carbon::parse($segment['arrival'])->diffForHumans(\Carbon\Carbon::parse($segment['departure']), \Carbon\CarbonInterface::DIFF_ABSOLUTE, true, 2) }})</div>
        <div style="display: block;">{{ $segment['carrier']['shortName'] }} {{ $segment['flight']['number'] }}@if(array_key_exists('ext', $segment) && array_key_exists('operationalDisclosure', $segment['ext'])) ({{ $segment['ext']['operationalDisclosure'] }})@endif</div>
        <div style="display: block;">{{ $segment['legs'][0]['aircraft']['shortName'] }}</div>
        <div style="display: block;">{{ collect($segment['bookingInfos'])->map(static fn (array $bookingInfo): string => ucfirst(strtolower($bookingInfo['cabin'])))->unique()->join(', ') }} ({{ collect($segment['bookingInfos'])->map(static fn (array $bookingInfo): string => ucfirst(strtolower($bookingInfo['bookingCode'])))->unique()->join(',') }})</div>
    </div>
</div>
@if($loop->count > 1 && ! $loop->last)
    <div style="display: flex; justify-content: center; margin: 0.5rem;"><small>LAYOVER IN {{ $segment['destination']['code'] }} ({{ strtoupper(\Carbon\Carbon::parse($segment['arrival'])->diffForHumans(\Carbon\Carbon::parse($slice['segments'][$loop->index + 1]['departure']), \Carbon\CarbonInterface::DIFF_ABSOLUTE, true, 2)) }})</small></div>
@endif
@endforeach
@endforeach
