@extends('layouts.platform')
@section('title', 'Email Templates')

@push('styles')
<style>
  .et-root { padding: 24px; max-width: 1200px; margin: 0 auto; }
  .et-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
  .et-title { font-size: 20px; font-weight: 700; color: #111827; }
  .et-subtitle { font-size: 13px; color: #6b7280; margin-top: 4px; }
  
  .et-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
  .et-card-hd { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
  .et-card-tit { font-size: 14px; font-weight: 600; color: #111827; }
  
  .et-table { width: 100%; border-collapse: collapse; }
  .et-table th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
  .et-table td { padding: 14px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
  .et-table tr:hover td { background: #f9fafb; }
  
  .et-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
  .et-badge.intervention { background: #fef3c7; color: #92400e; }
  .et-badge.winback { background: #fee2e2; color: #991b1b; }
  .et-badge.upsell { background: #dcfce7; color: #166534; }
  .et-badge.onboarding { background: #dbeafe; color: #1e40af; }
  .et-badge.general { background: #f3f4f6; color: #374151; }
  
  .et-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; text-decoration: none; }
  .et-btn-primary { background: #0EA5E9; color: #fff; }
  .et-btn-primary:hover { background: #0284c7; }
  .et-btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
  .et-btn-secondary:hover { background: #e5e7eb; }
  .et-btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
  .et-btn-danger:hover { background: #fee2e2; }
  .et-btn-sm { padding: 5px 10px; font-size: 11px; }
  
  .et-preview { max-width: 300px; font-size: 12px; color: #6b7280; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  
  .et-empty { text-align: center; padding: 60px 20px; color: #9ca3af; }
  .et-empty svg { width: 48px; height: 48px; margin-bottom: 16px; opacity: .4; }
  
  /* Modal */
  .et-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 100; align-items: center; justify-content: center; }
  .et-modal.active { display: flex; }
  .et-modal-box { background: #fff; border-radius: 12px; width: 600px; max-width: 92%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); }
  .et-modal-hd { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
  .et-modal-hd h3 { margin: 0; font-size: 15px; font-weight: 600; color: #111827; }
  .et-modal-body { padding: 20px; overflow-y: auto; }
  .et-form-group { margin-bottom: 16px; }
  .et-form-label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .et-form-label .required { color: #F43F5E; }
  .et-form-input, .et-form-select, .et-form-textarea { width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; outline: none; box-sizing: border-box; }
  .et-form-input:focus, .et-form-select:focus, .et-form-textarea:focus { border-color: #0EA5E9; }
  .et-form-textarea { resize: vertical; min-height: 120px; font-family: monospace; font-size: 12px; }
  .et-form-select { cursor: pointer; background: #fff; }
  .et-form-hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
  
  .et-vars { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
  .et-var { padding: 3px 8px; background: #f3f4f6; border-radius: 4px; font-size: 11px; font-family: monospace; color: #6b7280; cursor: pointer; transition: all .15s; }
  .et-var:hover { background: #0EA5E9; color: #fff; }
  
  .et-modal-ft { padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
@endphp

<div class="et-root">
    @if(session('success'))
        <div style="margin-bottom: 16px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; color: #166534; font-size: 13px; font-weight: 500;">
        ✓ {{ session('success') }}
        </div>
    @endif

  <div class="et-header">
    <div>
      <div class="et-title">Email Templates</div>
      <div class="et-subtitle">Create and manage reusable email templates for your campaigns</div>
    </div>
    <button class="et-btn et-btn-primary" onclick="openModal()">
      <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      New Template
    </button>
  </div>

  <div class="et-card">
    <div class="et-card-hd">
      <span class="et-card-tit">All Templates ({{ $templates->count() }})</span>
    </div>
    
    @if($templates->count() > 0)
      <table class="et-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Subject</th>
            <th>Preview</th>
            <th>Status</th>
            <th style="width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($templates as $template)
            <tr>
              <td>
                <div style="font-weight: 600; color: #111827;">{{ $template->name }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">ID: {{ $template->id }}</div>
              </td>
              <td>
                <span class="et-badge {{ $template->category }}">{{ ucfirst($template->category) }}</span>
              </td>
              <td style="max-width: 200px;">{{ $template->subject }}</td>
              <td>
                <div class="et-preview">{{ Str::limit(strip_tags($template->body), 80) }}</div>
              </td>
              <td>
                @if($template->is_active)
                  <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #10B981;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10B981;"></span> Active
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #9ca3af;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #9ca3af;"></span> Inactive
                  </span>
                @endif
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <button class="et-btn et-btn-secondary et-btn-sm" onclick="editTemplate({{ $template->id }}, '{{ addslashes($template->name) }}', '{{ addslashes($template->subject) }}', `{{ addslashes($template->body) }}`, '{{ $template->category }}', {{ $template->is_active ? 1 : 0 }})">
                    <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                  </button>
                  <form method="POST" action="{{ route('client.email.templates.destroy', $template) }}" style="display: inline;" onsubmit="return confirm('Delete this template?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="et-btn et-btn-danger et-btn-sm">
                      <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="et-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <p style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 4px;">No templates yet</p>
        <p style="font-size: 12px; margin-bottom: 16px;">Create your first email template to get started</p>
        <button class="et-btn et-btn-primary" onclick="openModal()">Create Template</button>
      </div>
    @endif
  </div>

  <div style="margin-top: 16px; text-align: center;">
    <a href="{{ route('client.layer.l5') }}" class="et-btn et-btn-secondary">
      <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to AI/ML Predictions
    </a>
  </div>

</div>

<!-- Create/Edit Modal -->
<div id="templateModal" class="et-modal">
  <div class="et-modal-box">
    <div class="et-modal-hd">
      <h3 id="modalTitle">New Email Template</h3>
      <button onclick="closeModal()" style="background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 18px;">✕</button>
    </div>
    <form id="templateForm" method="POST" action="{{ route('client.email.templates.store') }}">
      @csrf
      <input type="hidden" id="templateId" name="template_id">
      <input type="hidden" id="formMethod" name="_method" value="POST">
      
      <div class="et-modal-body">
        <div class="et-form-group">
          <label class="et-form-label">Template Name <span class="required">*</span></label>
          <input type="text" id="templateName" name="name" class="et-form-input" placeholder="e.g., Speaking Score Intervention" required>
        </div>
        
        <div class="et-form-group">
          <label class="et-form-label">Category <span class="required">*</span></label>
          <select id="templateCategory" name="category" class="et-form-select" required>
            <option value="general">General</option>
            <option value="intervention">Intervention</option>
            <option value="winback">Win-Back</option>
            <option value="upsell">Upsell</option>
            <option value="onboarding">Onboarding</option>
          </select>
        </div>
        
        <div class="et-form-group">
          <label class="et-form-label">Subject Line <span class="required">*</span></label>
          <input type="text" id="templateSubject" name="subject" class="et-form-input" placeholder="e.g., Personal Speaking Coaching Session" required>
        </div>
        
        <div class="et-form-group">
          <label class="et-form-label">Email Body <span class="required">*</span></label>
          <textarea id="templateBody" name="body" class="et-form-textarea" placeholder="Hi [[student_name]],

We've noticed your speaking score ([[speaking_score]]) has room for improvement..." required></textarea>
          <div class="et-form-hint">Click a variable below to insert it at cursor position</div>
          <div class="et-vars">
            <span class="et-var" onclick="insertVar('[[student_name]]')">[[student_name]]</span>
            <span class="et-var" onclick="insertVar('[[speaking_score]]')">[[speaking_score]]</span>
            <span class="et-var" onclick="insertVar('[[intent_score]]')">[[intent_score]]</span>
            <span class="et-var" onclick="insertVar('[[churn_score]]')">[[churn_score]]</span>
            <span class="et-var" onclick="insertVar('[[loyalty_score]]')">[[loyalty_score]]</span>
            <span class="et-var" onclick="insertVar('[[overall_score]]')">[[overall_score]]</span>
            <span class="et-var" onclick="insertVar('[[completed_courses]]')">[[completed_courses]]</span>
            <span class="et-var" onclick="insertVar('[[days_since_login]]')">[[days_since_login]]</span>
            <span class="et-var" onclick="insertVar('[[company_name]]')">[[company_name]]</span>
            <span class="et-var" onclick="insertVar('[[booking_link]]')">[[booking_link]]</span>
            <span class="et-var" onclick="insertVar('[[upgrade_link]]')">[[upgrade_link]]</span>
            <span class="et-var" onclick="insertVar('[[referral_link]]')">[[referral_link]]</span>
          </div>
        </div>
        
        <div class="et-form-group">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" id="templateActive" name="is_active" value="1" checked style="width: 16px; height: 16px; accent-color: #0EA5E9;">
            <span style="font-size: 13px; color: #374151;">Template is active</span>
          </label>
        </div>
      </div>
      
      <div class="et-modal-ft">
        <button type="button" class="et-btn et-btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="submit" class="et-btn et-btn-primary">Save Template</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById('templateForm').action = '{{ route("client.email.templates.store") }}';
  document.getElementById('formMethod').value = 'POST';
  document.getElementById('templateId').value = '';
  document.getElementById('modalTitle').textContent = 'New Email Template';
  document.getElementById('templateName').value = '';
  document.getElementById('templateCategory').value = 'general';
  document.getElementById('templateSubject').value = '';
  document.getElementById('templateBody').value = '';
  document.getElementById('templateActive').checked = true;
  document.getElementById('templateModal').classList.add('active');
}

function editTemplate(id, name, subject, body, category, isActive) {
  document.getElementById('templateForm').action = '{{ url("app/email-templates") }}/' + id;
  document.getElementById('formMethod').value = 'PUT';
  document.getElementById('templateId').value = id;
  document.getElementById('modalTitle').textContent = 'Edit Email Template';
  document.getElementById('templateName').value = name;
  document.getElementById('templateCategory').value = category;
  document.getElementById('templateSubject').value = subject;
  document.getElementById('templateBody').value = body;
  document.getElementById('templateActive').checked = isActive === 1;
  document.getElementById('templateModal').classList.add('active');
}

function closeModal() {
  document.getElementById('templateModal').classList.remove('active');
}

function insertVar(variable) {
  const textarea = document.getElementById('templateBody');
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const text = textarea.value;
  textarea.value = text.substring(0, start) + variable + text.substring(end);
  textarea.focus();
  textarea.selectionStart = textarea.selectionEnd = start + variable.length;
}

// Close modal on backdrop click
document.getElementById('templateModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeModal();
});
</script>

@endsection