<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Demo Booking</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1f2937; background: #f3f4f6; }
        .container { max-width: 600px; margin: 20px auto; }
        .header { background: linear-gradient(135deg, #4f8fff, #2563eb); color: white; padding: 28px; border-radius: 12px 12px 0 0; text-align: center; }
        .header h2 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 14px; }
        .content { background: #ffffff; padding: 28px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; }
        .highlight { background: #eff6ff; padding: 16px; border-radius: 8px; border-left: 4px solid #4f8fff; margin-bottom: 24px; font-size: 15px; }
        .section-title { color: #2563eb; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 24px 0 12px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
        .field { margin-bottom: 14px; }
        .label { font-weight: 600; color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .value { font-size: 15px; color: #111827; word-break: break-word; }
        .badge { display: inline-block; padding: 4px 12px; background: #dbeafe; color: #1e40af; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .footer { text-align: center; margin-top: 24px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔔 New Demo Booking Received</h2>
            <p>X Platforms — Intelligence Layer</p>
        </div>
        <div class="content">
            <div class="highlight">
                <strong>{{ $demoData['first_name'] }} {{ $demoData['last_name'] }}</strong> from 
                <strong>{{ $demoData['company_name'] }}</strong> has requested a demo.
            </div>

            <div class="section-title">👤 Contact Details</div>
            <div class="field">
                <div class="label">Full Name</div>
                <div class="value">{{ $demoData['first_name'] }} {{ $demoData['last_name'] }}</div>
            </div>
            <div class="field">
                <div class="label">Email</div>
                <div class="value">{{ $demoData['email'] }}</div>
            </div>
            <div class="field">
                <div class="label">Company</div>
                <div class="value">{{ $demoData['company_name'] }}</div>
            </div>
            <div class="field">
                <div class="label">Job Title</div>
                <div class="value">{{ $demoData['job_title'] }}</div>
            </div>
            <div class="field">
                <div class="label">Company Size</div>
                <div class="value">{{ $demoData['company_size'] ?? 'Not provided' }}</div>
            </div>
            <div class="field">
                <div class="label">Industry</div>
                <div class="value"><span class="badge">{{ $demoData['industry'] ?? 'Not provided' }}</span></div>
            </div>

            <div class="section-title">📅 Demo Schedule</div>
            <div class="field">
                <div class="label">Date</div>
                <div class="value">{{ $demoData['demo_date'] ?? 'Not selected' }}</div>
            </div>
            <div class="field">
                <div class="label">Time</div>
                <div class="value">{{ $demoData['demo_time'] ?? 'Not selected' }} {{ $demoData['timezone'] ? '(' . $demoData['timezone'] . ')' : '' }}</div>
            </div>

            <div class="section-title">💼 Business Context</div>
            <div class="field">
                <div class="label">Monthly Active Customers</div>
                <div class="value">{{ $demoData['monthly_active_customers'] ?? 'Not provided' }}</div>
            </div>
            <div class="field">
                <div class="label">Monthly Revenue</div>
                <div class="value">{{ $demoData['monthly_revenue'] ?? 'Not provided' }}</div>
            </div>
            <div class="field">
                <div class="label">Primary Challenge</div>
                <div class="value">{{ $demoData['primary_challenge'] ?? 'Not provided' }}</div>
            </div>
            <div class="field">
                <div class="label">Data Sources</div>
                <div class="value">{{ $demoData['data_sources'] ?: 'Not provided' }}</div>
            </div>
            <div class="field">
                <div class="label">Demo Notes</div>
                <div class="value">{{ $demoData['demo_notes'] ?: 'Not provided' }}</div>
            </div>

            <div class="footer">
                Submitted on {{ now()->format('F j, Y \a\t g:i A') }} via X Platforms
            </div>
        </div>
    </div>
</body>
</html>