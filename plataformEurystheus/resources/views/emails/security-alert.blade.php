<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alert - Eurystheus AI</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header.high {
            background: linear-gradient(135deg, #fd7e14, #e65500);
        }
        .header.medium {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        .header.low {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .alert-details {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .alert-details.high {
            border-left-color: #fd7e14;
        }
        .alert-details.medium {
            border-left-color: #ffc107;
        }
        .alert-details.low {
            border-left-color: #17a2b8;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }
        .detail-value {
            color: #6c757d;
            text-align: right;
        }
        .severity-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .severity-critical {
            background: #dc3545;
            color: white;
        }
        .severity-high {
            background: #fd7e14;
            color: white;
        }
        .severity-medium {
            background: #ffc107;
            color: #212529;
        }
        .severity-low {
            background: #17a2b8;
            color: white;
        }
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 0 10px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .timestamp {
            color: #6c757d;
            font-size: 14px;
            font-style: italic;
        }
        .description {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .recommendations {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .recommendations h4 {
            color: #0c5460;
            margin-top: 0;
        }
        .recommendations ul {
            margin-bottom: 0;
        }
        .recommendations li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ strtolower($alert['severity']) }}">
            <div class="alert-icon">🛡️</div>
            <h1>Security Alert</h1>
            <p>{{ $alert['type'] }} Detected</p>
        </div>
        
        <div class="content">
            <p>A security event has been detected on the Eurystheus AI platform that requires your attention.</p>
            
            <div class="alert-details {{ strtolower($alert['severity']) }}">
                <div class="detail-row">
                    <span class="detail-label">Alert Type:</span>
                    <span class="detail-value">{{ $alert['type'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Severity:</span>
                    <span class="detail-value">
                        <span class="severity-badge severity-{{ strtolower($alert['severity']) }}">
                            {{ $alert['severity'] }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Detected:</span>
                    <span class="detail-value timestamp">{{ $alert['detected_at'] }}</span>
                </div>
                @if(isset($alert['source_ip']))
                <div class="detail-row">
                    <span class="detail-label">Source IP:</span>
                    <span class="detail-value">{{ $alert['source_ip'] }}</span>
                </div>
                @endif
                @if(isset($alert['user_agent']))
                <div class="detail-row">
                    <span class="detail-label">User Agent:</span>
                    <span class="detail-value">{{ Str::limit($alert['user_agent'], 50) }}</span>
                </div>
                @endif
                @if(isset($alert['endpoint']))
                <div class="detail-row">
                    <span class="detail-label">Endpoint:</span>
                    <span class="detail-value">{{ $alert['endpoint'] }}</span>
                </div>
                @endif
                @if(isset($alert['attack_count']))
                <div class="detail-row">
                    <span class="detail-label">Attack Count:</span>
                    <span class="detail-value">{{ $alert['attack_count'] }}</span>
                </div>
                @endif
            </div>
            
            @if(isset($alert['description']))
            <div class="description">
                <strong>Description:</strong> {{ $alert['description'] }}
            </div>
            @endif
            
            @if(isset($alert['details']) && is_array($alert['details']))
            <div class="alert-details {{ strtolower($alert['severity']) }}">
                <h4>Additional Details</h4>
                @foreach($alert['details'] as $key => $value)
                <div class="detail-row">
                    <span class="detail-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                    <span class="detail-value">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                </div>
                @endforeach
            </div>
            @endif
            
            <div class="recommendations">
                <h4>Recommended Actions</h4>
                <ul>
                    @if($alert['severity'] === 'critical')
                    <li>Immediately review and investigate this security incident</li>
                    <li>Consider temporarily blocking the source IP if applicable</li>
                    <li>Check system logs for related suspicious activity</li>
                    <li>Verify system integrity and user account security</li>
                    @elseif($alert['severity'] === 'high')
                    <li>Review the security incident within the next hour</li>
                    <li>Monitor for additional similar attacks</li>
                    <li>Consider implementing additional security measures</li>
                    @elseif($alert['severity'] === 'medium')
                    <li>Review the security incident when convenient</li>
                    <li>Monitor trends and patterns in security events</li>
                    @else
                    <li>Log the incident for future reference</li>
                    <li>Review security metrics periodically</li>
                    @endif
                    <li>Access the security dashboard for more details</li>
                    <li>Update security configurations if necessary</li>
                </ul>
            </div>
            
            <div class="actions">
                <a href="{{ url('/admin/security/dashboard') }}" class="btn">View Security Dashboard</a>
                <a href="{{ url('/admin/security/alerts') }}" class="btn btn-danger">Manage Alerts</a>
            </div>
        </div>
        
        <div class="footer">
            <p>This alert was generated automatically by the Eurystheus AI Security Monitoring System.</p>
            <p>For urgent security matters, please contact your system administrator immediately.</p>
            <p><strong>Eurystheus AI</strong> - Advanced AI-Powered Security Platform</p>
        </div>
    </div>
</body>
</html>
