{{--
    Mock Master Helper — tabbed revenue lists, shared by Sales · Close & grow
    and Retention · Renew & win back.

    Expects:
      $tabs       — [key => ['label', 'count', 'rows', 'dataset']]; the key
                    (convert | abandoned | renewals | winback) picks the columns
      $ariaLabel  — label for the tab list
      $mmAvColor, $mmInitial — avatar helpers from mock-master-helper.blade.php
--}}
@php $scoreKind = fn ($v) => $v >= 70 ? 'good' : ($v >= 50 ? 'info' : 'warn'); @endphp
<div class="mm-scr-tabs" role="tablist" aria-label="{{ $ariaLabel }}">
    @foreach($tabs as $tk => $tab)
    <button type="button" role="tab" class="mm-scr-tab {{ $loop->first ? 'on' : '' }}" data-ch="{{ $tk }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" onclick="mmScriptChannel(this)">{{ $tab['label'] }} <span class="mm-scr-count">{{ number_format($tab['count']) }}</span></button>
    @endforeach
</div>

@foreach($tabs as $tk => $tab)
<div class="mm-scr-list" data-ch="{{ $tk }}" @if(!$loop->first) style="display:none" @endif>
    @if(empty($tab['rows']))
    <div class="act"><div class="act-t" style="color:var(--g3)">Nobody matches this right now.</div></div>
    @else
    <table class="dtbl">
        <thead><tr>
            <th>Student</th>
            @if($tk === 'convert')<th>Score</th><th>Why</th><th>Last active</th>
            @elseif($tk === 'abandoned')<th>Package</th><th>Amount</th><th>Attempted</th>
            @elseif($tk === 'renewals')<th>Package</th><th>Expires</th><th>Value</th>
            @else<th>Last package</th><th>Expired</th><th>Value</th>
            @endif
            <th>Next step</th>
        </tr></thead>
        <tbody>
            @foreach($tab['rows'] as $r)
            <tr>
                <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div><div class="bh-acct-n">{{ $r['name'] }}</div>@if($tk !== 'convert')<div class="bh-acct-c">{{ $r['signals'] }}</div>@endif</div></div></td>
                @if($tk === 'convert')
                <td><span class="bh-pill {{ $scoreKind($r['score']) }}">{{ $r['score'] }}</span></td>
                <td class="cg-why">{{ $r['signals'] }}</td>
                <td>{{ $r['lastActive'] }}</td>
                @elseif($tk === 'abandoned')
                <td>{{ $r['package'] }}</td><td>{{ $r['amount'] }}</td><td>{{ $r['attempted'] }}</td>
                @elseif($tk === 'renewals')
                <td>{{ $r['package'] }}</td><td>{{ $r['expires'] }}</td><td>{{ $r['amount'] }}</td>
                @else
                <td>{{ $r['package'] }}</td><td>{{ $r['expired'] }}</td><td>{{ $r['amount'] }}</td>
                @endif
                <td class="cg-next">{{ $r['action'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button type="button" class="qk cg-all" onclick="mmOpenDataset('{{ $tab['dataset'] }}', '{{ $tab['label'] }}')">{{ $tab['count'] > count($tab['rows']) ? 'View all ' . number_format($tab['count']) . ' with contact details →' : 'View with contact details →' }}</button>
    @endif
</div>
@endforeach
