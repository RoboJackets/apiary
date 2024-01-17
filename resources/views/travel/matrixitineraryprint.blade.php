<html>
<head>
    <title>Itinerary Request for {{ $assignment->user->full_name }}</title>
</head>
<body>
<h1>Itinerary Request for {{ $assignment->user->full_name }}</h1>
@if($assignment->travel->airfare_policy['delta'] === true && ($assignment->travel->airfare_policy['coach'] === true || $assignment->travel->airfare_policy['fare_class'] === true))
    <p>
        Please book the lowest-cost available tickets for the flights below in Delta Main Cabin economy class, excluding basic economy.
    </p>
@elseif($assignment->travel->airfare_policy['delta'] === true && $assignment->travel->airfare_policy['coach'] === true)
    <p>
        Please book the lowest-cost available tickets for the flights below in Delta Main Cabin economy class.
    </p>
@elseif($assignment->travel->airfare_policy['coach'] === true)
    <p>
        Please book the lowest-cost available tickets for the flights below in economy class.
    </p>
@endif
<table style="width: 100%">
    @foreach($assignment->matrix_itinerary['itinerary']['slices'] as $slice)
        <tr>
            <td colspan="2">
                <h3 @if(!$loop->first) style="margin-top: 3rem" @endif>{{ $slice['origin']['city']['name'] }} ({{ $slice['origin']['code'] }}) to {{ $slice['destination']['city']['name'] }} ({{ $slice['destination']['code'] }}) on {{ \Carbon\Carbon::parse($slice['departure'])->format('D, M j, Y') }}</h3>
            </td>
        </tr>

        @foreach($slice['segments'] as $segment)
                <tr>
                    <td>
                        <strong>{{ $segment['origin']['city']['name'] }} ({{$segment['origin']['code']}}) to {{ $segment['destination']['city']['name'] }} ({{ $segment['destination']['code'] }})</strong> on {{ \Carbon\Carbon::parse($segment['departure'])->format('D, M j, Y') }}
                    </td>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($segment['departure'])->format('g:i A') }}</strong> to <strong>{{ \Carbon\Carbon::parse($segment['arrival'])->format('g:i A') }}</strong> ({{ \Carbon\Carbon::parse($segment['arrival'])->diffForHumans(\Carbon\Carbon::parse($segment['departure']), \Carbon\CarbonInterface::DIFF_ABSOLUTE, true, 2) }})
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ $segment['carrier']['shortName'] }} {{ $segment['flight']['number'] }}@if(array_key_exists('ext', $segment) && array_key_exists('operationalDisclosure', $segment['ext'])) ({{ $segment['ext']['operationalDisclosure'] }})@endif
                    </td>
                    <td>
                        {{ $segment['legs'][0]['aircraft']['shortName'] }}
                    </td>
                </tr>
            @if($loop->count > 1 && ! $loop->last)
                <tr>
                    <td colspan="2" style="text-align: center; padding: 0.6rem">
                        LAYOVER IN {{ $segment['destination']['code'] }} ({{ strtoupper(\Carbon\Carbon::parse($segment['arrival'])->diffForHumans(\Carbon\Carbon::parse($slice['segments'][$loop->index + 1]['departure']), \Carbon\CarbonInterface::DIFF_ABSOLUTE, true, 2)) }})
                    </td>
                </tr>
            @endif
        @endforeach
    @endforeach
</table>
<p style="margin-top: 3rem">
    <strong>Estimated total: ${{ number_format(\App\Util\Matrix::getHighestDisplayPrice($assignment->matrix_itinerary), 2) }}</strong>
</p>
</body></html>
