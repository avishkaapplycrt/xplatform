{{-- One Data Collection panel drawn from App\Services\DataCollectionStats:
     $p = [title, color, note, stats, bars, tables] or [title, note, empty]. --}}
@php $c = $p['color'] ?? '#14b8a6'; @endphp
<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
  <div style="width:10px;height:10px;border-radius:50%;background:{{ $c }}"></div>
  <span style="font-size:15px;font-weight:700;color:#111827;letter-spacing:.3px">{{ $p['title'] }}</span>
  <div style="flex:1;height:1px;background:#e5e7eb;margin-left:8px"></div>
  @unless(isset($p['empty']))
    <span style="font-size:12px;font-weight:600;color:{{ $c }};background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:2px 10px">Live</span>
  @endunless
</div>
<p style="font-size:12px;color:#9ca3af;margin-bottom:20px">{{ $p['note'] }}</p>

@if(isset($p['empty']))
  @include('client.data-collection.partials.unavailable', ['message' => $p['empty'], 'tag' => $p['tag'] ?? 'No data yet',
      'actionUrl' => $p['action'][0] ?? null, 'actionLabel' => $p['action'][1] ?? null])
@else
  @if(!empty($p['stats']))
  <div class="dc-stats">
    @foreach($p['stats'] as [$label, $value])
      <div class="panel-card" style="text-align:center;padding:16px 8px">
        <p style="font-size:20px;font-weight:800;color:{{ $c }};line-height:1.15">{{ $value }}</p>
        <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">{{ $label }}</p>
      </div>
    @endforeach
  </div>
  @endif

  <div class="dc-blocks">
    @foreach($p['bars'] ?? [] as $bar)
      <div class="panel-card" style="padding:16px 20px">
        <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px">{{ $bar['title'] }}</p>
        @foreach($bar['rows'] as [$label, $valueText, $pct])
          <div style="margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
              <span style="color:#374151">{{ $label }}</span><span style="font-weight:600;color:{{ $c }}">{{ $valueText }}</span>
            </div>
            <div style="height:6px;border-radius:999px;background:#f1f5f9"><div style="height:6px;border-radius:999px;background:{{ $c }};width:{{ max(2, min(100, $pct)) }}%"></div></div>
          </div>
        @endforeach
      </div>
    @endforeach

    @foreach($p['tables'] ?? [] as $table)
      <div class="panel-card" style="padding:16px 20px;overflow-x:auto">
        <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px">{{ $table['title'] }}</p>
        @if(empty($table['rows']))
          <p style="font-size:12px;color:#9ca3af">{{ $table['empty'] ?? 'Nothing to show yet.' }}</p>
        @else
          <table class="dc-table">
            <thead><tr>@foreach($table['cols'] as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
            <tbody>
              @foreach($table['rows'] as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    @endforeach
  </div>
@endif
