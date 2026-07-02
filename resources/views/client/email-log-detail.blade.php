<div class="el-detail-row">
  <div class="el-detail-label">ID</div>
  <div class="el-detail-value">#{{ $log->id }}</div>
</div>
<div class="el-detail-row">
  <div class="el-detail-label">Recipient</div>
  <div class="el-detail-value">
    {{ $log->recipient_name ?? 'Unknown' }}<br>
    <span style="color:#0EA5E9;">{{ $log->email_address }}</span>
  </div>
</div>
<div class="el-detail-row">
  <div class="el-detail-label">Subject</div>
  <div class="el-detail-value">{{ $log->subject ?? 'No subject' }}</div>
</div>
<div class="el-detail-row">
  <div class="el-detail-label">Type</div>
  <div class="el-detail-value">
    <span class="el-badge" style="background:{{ $log->type_bg_color }}; color:{{ $log->type_color }};">
      {{ ucfirst($log->type) }}
      @if($log->type == 'bulk') ({{ $log->bulk_count }} recipients) @endif
    </span>
  </div>
</div>
<div class="el-detail-row">
  <div class="el-detail-label">Status</div>
  <div class="el-detail-value">
    <span class="el-badge" style="background:{{ $log->status_bg_color }}; color:{{ $log->status_color }};">
      {{ ucfirst($log->status) }}
    </span>
    @if($log->error_message)
      <div style="color:#F43F5E; margin-top:4px; font-size:12px;">
        <strong>Error:</strong> {{ $log->error_message }}
      </div>
    @endif
  </div>
</div>
@if($log->template)
<div class="el-detail-row">
  <div class="el-detail-label">Template</div>
  <div class="el-detail-value">{{ $log->template->name }} ({{ $log->template->category }})</div>
</div>
@endif
<div class="el-detail-row">
  <div class="el-detail-label">Sent At</div>
  <div class="el-detail-value">{{ $log->sent_at ? $log->sent_at->format('F d, Y H:i:s') : 'Not sent' }}</div>
</div>
@if($log->delivered_at)
<div class="el-detail-row">
  <div class="el-detail-label">Delivered At</div>
  <div class="el-detail-value">{{ $log->delivered_at->format('F d, Y H:i:s') }}</div>
</div>
@endif
@if($log->opened_at)
<div class="el-detail-row">
  <div class="el-detail-label">Opened At</div>
  <div class="el-detail-value" style="color:#10B981;">
    {{ $log->opened_at->format('F d, Y H:i:s') }}
    ({{ $log->opened_at->diffForHumans() }})
    @if($log->time_to_open_minutes)
      <br><span style="font-size:11px; color:#6b7280;">Time to open: {{ $log->time_to_open_minutes }} min</span>
    @endif
  </div>
</div>
@endif
@if($log->clicked_at)
<div class="el-detail-row">
  <div class="el-detail-label">Clicked At</div>
  <div class="el-detail-value">{{ $log->clicked_at->format('F d, Y H:i:s') }}</div>
</div>
@endif
@if($log->converted_at)
<div class="el-detail-row">
  <div class="el-detail-label">Converted At</div>
  <div class="el-detail-value" style="color:#10B981;">{{ $log->converted_at->format('F d, Y H:i:s') }}</div>
</div>
@endif
@if($log->unsubscribed_at)
<div class="el-detail-row">
  <div class="el-detail-label">Unsubscribed At</div>
  <div class="el-detail-value" style="color:#F43F5E;">{{ $log->unsubscribed_at->format('F d, Y H:i:s') }}</div>
</div>
@endif
@if($log->device_type)
<div class="el-detail-row">
  <div class="el-detail-label">Device</div>
  <div class="el-detail-value">{{ ucfirst($log->device_type) }}</div>
</div>
@endif
@if($log->ip_address)
<div class="el-detail-row">
  <div class="el-detail-label">IP Address</div>
  <div class="el-detail-value">{{ $log->ip_address }}</div>
</div>
@endif
<div class="el-detail-row">
  <div class="el-detail-label">Created</div>
  <div class="el-detail-value">{{ $log->created_at->format('F d, Y H:i:s') }}</div>
</div>
<div class="el-detail-row" style="flex-direction:column;">
  <div class="el-detail-label" style="margin-bottom:8px;">Email Body</div>
  <div class="el-detail-body">{{ strip_tags($log->body ?? 'No content') }}</div>
</div>