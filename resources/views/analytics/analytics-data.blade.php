<?php
// resources/views/analytics/analytics-data.blade.php
?>
@extends('layouts.client')

@section('title', 'Analytics Data')
@section('header', $site->name . ' - Analytics')

@section('content')
<div class="space-y-6">
    <!-- Realtime Badge -->
    <div class="flex items-center gap-2">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
        <span class="text-sm font-medium text-emerald-700">
            {{ $realtimeVisitors }} active visitors now
        </span>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Pageviews (7d)</p>
                <i class="fas fa-eye text-blue-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->pageviews ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Unique Visitors</p>
                <i class="fas fa-users text-emerald-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->unique_visitors ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Sessions</p>
                <i class="fas fa-clock text-purple-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->sessions ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Bounce Rate</p>
                <i class="fas fa-percentage text-orange-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->bounce_rate ?? 0 }}%</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Traffic Overview (Last 7 Days)</h3>
            
            @php
                $labels = $chartData->pluck('date')->map(fn($d) => $d->format('M d'))->toArray();
                $pageviewsData = $chartData->pluck('pageviews')->toArray();
                $visitorsData = $chartData->pluck('unique_visitors')->toArray();
                
                $maxValue = max(max($pageviewsData), max($visitorsData), 1);
                $chartHeight = 250;
                $chartWidth = 100;
                $barWidth = 8;
            @endphp
            
            @if(count($labels) > 0)
            <div style="height: 300px; position: relative;">
                <!-- Simple CSS Bar Chart -->
                <div style="display: flex; align-items: flex-end; justify-content: space-around; height: 250px; padding: 20px 10px 40px 10px; border-left: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; position: relative;">
                    
                    @foreach($labels as $index => $label)
                        @php
                            $pvHeight = ($pageviewsData[$index] / $maxValue) * 200;
                            $uvHeight = ($visitorsData[$index] / $maxValue) * 200;
                        @endphp
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                            <div style="display: flex; align-items: flex-end; gap: 2px; height: 200px;">
                                <!-- Pageviews bar -->
                                <div style="width: 12px; height: {{ $pvHeight }}px; background: #10b981; border-radius: 3px 3px 0 0; min-height: 2px; transition: height 0.3s;" title="Pageviews: {{ $pageviewsData[$index] }}"></div>
                                <!-- Visitors bar -->
                                <div style="width: 12px; height: {{ $uvHeight }}px; background: #3b82f6; border-radius: 3px 3px 0 0; min-height: 2px; transition: height 0.3s;" title="Visitors: {{ $visitorsData[$index] }}"></div>
                            </div>
                            <span style="font-size: 11px; color: #6b7280; margin-top: 8px;">{{ $label }}</span>
                        </div>
                    @endforeach
                    
                </div>
                
                <!-- Legend -->
                <div style="display: flex; justify-content: center; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Pageviews</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Unique Visitors</span>
                    </div>
                </div>
            </div>
            @else
            <div style="text-align: center; padding: 80px 20px; color: #9ca3af;">
                <p>No data available for the last 7 days</p>
            </div>
            @endif
        </div>

        <!-- Countries -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Top Countries</h3>
            <div class="space-y-3">
                @php
                $countryNames = [
                    'US' => 'United States', 'IN' => 'India', 'GB' => 'United Kingdom', 
                    'CA' => 'Canada', 'AU' => 'Australia', 'DE' => 'Germany', 
                    'FR' => 'France', 'BR' => 'Brazil', 'JP' => 'Japan', 
                    'CN' => 'China', 'PK' => 'Pakistan', 'BD' => 'Bangladesh',
                    'NP' => 'Nepal', 'LK' => 'Sri Lanka', 'AE' => 'UAE',
                    'SA' => 'Saudi Arabia', 'SG' => 'Singapore', 'MY' => 'Malaysia',
                    'ID' => 'Indonesia', 'PH' => 'Philippines', 'TH' => 'Thailand',
                    'KR' => 'South Korea', 'RU' => 'Russia', 'IT' => 'Italy',
                    'ES' => 'Spain', 'NL' => 'Netherlands', 'SE' => 'Sweden',
                    'NZ' => 'New Zealand', 'ZA' => 'South Africa', 'MX' => 'Mexico',
                    'AR' => 'Argentina', 'CO' => 'Colombia', 'CL' => 'Chile',
                    'PE' => 'Peru', 'VE' => 'Venezuela', 'EC' => 'Ecuador',
                    'BO' => 'Bolivia', 'PY' => 'Paraguay', 'UY' => 'Uruguay',
                    'GY' => 'Guyana', 'SR' => 'Suriname', 'GF' => 'French Guiana',
                    'FK' => 'Falkland Islands', 'NG' => 'Nigeria', 'KE' => 'Kenya',
                    'GH' => 'Ghana', 'UG' => 'Uganda', 'TZ' => 'Tanzania',
                    'ZW' => 'Zimbabwe', 'ZM' => 'Zambia', 'MW' => 'Malawi',
                    'MZ' => 'Mozambique', 'MG' => 'Madagascar', 'MU' => 'Mauritius',
                    'SC' => 'Seychelles', 'KM' => 'Comoros', 'RE' => 'Reunion',
                    'YT' => 'Mayotte', 'SH' => 'Saint Helena', 'CV' => 'Cape Verde',
                    'GW' => 'Guinea-Bissau', 'GN' => 'Guinea', 'SL' => 'Sierra Leone',
                    'LR' => 'Liberia', 'CI' => 'Ivory Coast', 'BF' => 'Burkina Faso',
                    'ML' => 'Mali', 'NE' => 'Niger', 'TD' => 'Chad', 'SD' => 'Sudan',
                    'SS' => 'South Sudan', 'ET' => 'Ethiopia', 'SO' => 'Somalia',
                    'DJ' => 'Djibouti', 'ER' => 'Eritrea', 'CG' => 'Congo',
                    'CD' => 'DR Congo', 'GA' => 'Gabon', 'GQ' => 'Equatorial Guinea',
                    'ST' => 'Sao Tome and Principe', 'CM' => 'Cameroon', 'CF' => 'Central African Republic',
                    'LY' => 'Libya', 'TN' => 'Tunisia', 'DZ' => 'Algeria',
                    'MA' => 'Morocco', 'EH' => 'Western Sahara', 'MR' => 'Mauritania',
                    'SN' => 'Senegal', 'GM' => 'Gambia', 'TG' => 'Togo',
                    'BJ' => 'Benin', 'EG' => 'Egypt', 'RW' => 'Rwanda',
                    'BI' => 'Burundi', 'AO' => 'Angola', 'BW' => 'Botswana',
                    'NA' => 'Namibia', 'LS' => 'Lesotho', 'SZ' => 'Eswatini',
                    'MQ' => 'Martinique', 'GP' => 'Guadeloupe', 'GF' => 'French Guiana',
                    'PF' => 'French Polynesia', 'NC' => 'New Caledonia', 'PM' => 'Saint Pierre and Miquelon',
                    'WF' => 'Wallis and Futuna', 'TF' => 'French Southern Territories',
                    'AX' => 'Aland Islands', 'FO' => 'Faroe Islands', 'GL' => 'Greenland',
                    'IS' => 'Iceland', 'SJ' => 'Svalbard and Jan Mayen', 'NO' => 'Norway',
                    'FI' => 'Finland', 'DK' => 'Denmark', 'SE' => 'Sweden',
                    'EE' => 'Estonia', 'LV' => 'Latvia', 'LT' => 'Lithuania',
                    'BY' => 'Belarus', 'UA' => 'Ukraine', 'MD' => 'Moldova',
                    'PL' => 'Poland', 'CZ' => 'Czech Republic', 'SK' => 'Slovakia',
                    'HU' => 'Hungary', 'RO' => 'Romania', 'BG' => 'Bulgaria',
                    'RS' => 'Serbia', 'ME' => 'Montenegro', 'XK' => 'Kosovo',
                    'AL' => 'Albania', 'MK' => 'North Macedonia', 'GR' => 'Greece',
                    'SI' => 'Slovenia', 'HR' => 'Croatia', 'BA' => 'Bosnia and Herzegovina',
                    'TR' => 'Turkey', 'CY' => 'Cyprus', 'MT' => 'Malta',
                    'IE' => 'Ireland', 'PT' => 'Portugal', 'ES' => 'Spain',
                    'AD' => 'Andorra', 'MC' => 'Monaco', 'SM' => 'San Marino',
                    'VA' => 'Vatican City', 'LI' => 'Liechtenstein', 'CH' => 'Switzerland',
                    'AT' => 'Austria', 'BE' => 'Belgium', 'LU' => 'Luxembourg',
                    'NL' => 'Netherlands', 'DE' => 'Germany', 'FR' => 'France',
                    'GB' => 'United Kingdom', 'IM' => 'Isle of Man', 'JE' => 'Jersey',
                    'GG' => 'Guernsey', 'GI' => 'Gibraltar', 'ES' => 'Spain',
                    'PT' => 'Portugal', 'IT' => 'Italy', 'VA' => 'Vatican City',
                    'SM' => 'San Marino', 'MT' => 'Malta', 'GR' => 'Greece',
                    'CY' => 'Cyprus', 'BG' => 'Bulgaria', 'RO' => 'Romania',
                    'HU' => 'Hungary', 'SK' => 'Slovakia', 'CZ' => 'Czech Republic',
                    'PL' => 'Poland', 'LT' => 'Lithuania', 'LV' => 'Latvia',
                    'EE' => 'Estonia', 'FI' => 'Finland', 'SE' => 'Sweden',
                    'NO' => 'Norway', 'DK' => 'Denmark', 'IS' => 'Iceland',
                    'IE' => 'Ireland', 'GB' => 'United Kingdom', 'NL' => 'Netherlands',
                    'BE' => 'Belgium', 'LU' => 'Luxembourg', 'DE' => 'Germany',
                    'CH' => 'Switzerland', 'AT' => 'Austria', 'LI' => 'Liechtenstein',
                    'FR' => 'France', 'MC' => 'Monaco', 'AD' => 'Andorra',
                    'ES' => 'Spain', 'PT' => 'Portugal', 'IT' => 'Italy',
                    'SM' => 'San Marino', 'VA' => 'Vatican City', 'MT' => 'Malta',
                    'SI' => 'Slovenia', 'HR' => 'Croatia', 'BA' => 'Bosnia and Herzegovina',
                    'RS' => 'Serbia', 'ME' => 'Montenegro', 'AL' => 'Albania',
                    'MK' => 'North Macedonia', 'GR' => 'Greece', 'BG' => 'Bulgaria',
                    'RO' => 'Romania', 'TR' => 'Turkey', 'CY' => 'Cyprus',
                    'GE' => 'Georgia', 'AM' => 'Armenia', 'AZ' => 'Azerbaijan',
                    'BY' => 'Belarus', 'UA' => 'Ukraine', 'MD' => 'Moldova',
                    'RU' => 'Russia', 'KZ' => 'Kazakhstan', 'UZ' => 'Uzbekistan',
                    'KG' => 'Kyrgyzstan', 'TJ' => 'Tajikistan', 'TM' => 'Turkmenistan',
                    'AF' => 'Afghanistan', 'PK' => 'Pakistan', 'IN' => 'India',
                    'NP' => 'Nepal', 'BT' => 'Bhutan', 'BD' => 'Bangladesh',
                    'MM' => 'Myanmar', 'LK' => 'Sri Lanka', 'MV' => 'Maldives',
                    'TH' => 'Thailand', 'LA' => 'Laos', 'VN' => 'Vietnam',
                    'KH' => 'Cambodia', 'MY' => 'Malaysia', 'BN' => 'Brunei',
                    'ID' => 'Indonesia', 'PH' => 'Philippines', 'SG' => 'Singapore',
                    'TL' => 'Timor-Leste', 'CN' => 'China', 'MN' => 'Mongolia',
                    'KP' => 'North Korea', 'KR' => 'South Korea', 'JP' => 'Japan',
                    'TW' => 'Taiwan', 'HK' => 'Hong Kong', 'MO' => 'Macau',
                    'IO' => 'British Indian Ocean Territory', 'CX' => 'Christmas Island',
                    'CC' => 'Cocos Islands', 'NF' => 'Norfolk Island', 'AU' => 'Australia',
                    'NZ' => 'New Zealand', 'PG' => 'Papua New Guinea', 'SB' => 'Solomon Islands',
                    'VU' => 'Vanuatu', 'NC' => 'New Caledonia', 'PF' => 'French Polynesia',
                    'WF' => 'Wallis and Futuna', 'WS' => 'Samoa', 'AS' => 'American Samoa',
                    'KI' => 'Kiribati', 'NR' => 'Nauru', 'TV' => 'Tuvalu', 'FJ' => 'Fiji',
                    'TO' => 'Tonga', 'TK' => 'Tokelau', 'NU' => 'Niue', 'CK' => 'Cook Islands',
                    'PN' => 'Pitcairn Islands', 'GU' => 'Guam', 'MP' => 'Northern Mariana Islands',
                    'UM' => 'US Minor Outlying Islands', 'US' => 'United States',
                    'CA' => 'Canada', 'MX' => 'Mexico', 'GT' => 'Guatemala', 'BZ' => 'Belize',
                    'SV' => 'El Salvador', 'HN' => 'Honduras', 'NI' => 'Nicaragua',
                    'CR' => 'Costa Rica', 'PA' => 'Panama', 'BS' => 'Bahamas',
                    'CU' => 'Cuba', 'JM' => 'Jamaica', 'HT' => 'Haiti', 'DO' => 'Dominican Republic',
                    'PR' => 'Puerto Rico', 'VI' => 'US Virgin Islands', 'VG' => 'British Virgin Islands',
                    'AI' => 'Anguilla', 'KN' => 'Saint Kitts and Nevis', 'AG' => 'Antigua and Barbuda',
                    'MS' => 'Montserrat', 'GP' => 'Guadeloupe', 'MQ' => 'Martinique',
                    'LC' => 'Saint Lucia', 'VC' => 'Saint Vincent and the Grenadines',
                    'BB' => 'Barbados', 'GD' => 'Grenada', 'TT' => 'Trinidad and Tobago',
                    'AW' => 'Aruba', 'CW' => 'Curacao', 'BQ' => 'Caribbean Netherlands',
                    'SX' => 'Sint Maarten', 'MF' => 'Saint Martin', 'BL' => 'Saint Barthelemy',
                    'CO' => 'Colombia', 'VE' => 'Venezuela', 'GY' => 'Guyana', 'SR' => 'Suriname',
                    'GF' => 'French Guiana', 'EC' => 'Ecuador', 'PE' => 'Peru', 'BO' => 'Bolivia',
                    'PY' => 'Paraguay', 'CL' => 'Chile', 'AR' => 'Argentina', 'UY' => 'Uruguay',
                    'FK' => 'Falkland Islands', 'GS' => 'South Georgia and South Sandwich Islands',
                    'AQ' => 'Antarctica', 'BV' => 'Bouvet Island', 'HM' => 'Heard Island and McDonald Islands',
                    'TF' => 'French Southern Territories', 'EH' => 'Western Sahara', 'MA' => 'Morocco',
                    'DZ' => 'Algeria', 'TN' => 'Tunisia', 'LY' => 'Libya', 'EG' => 'Egypt',
                    'SD' => 'Sudan', 'SS' => 'South Sudan', 'ET' => 'Ethiopia', 'ER' => 'Eritrea',
                    'DJ' => 'Djibouti', 'SO' => 'Somalia', 'KE' => 'Kenya', 'UG' => 'Uganda',
                    'RW' => 'Rwanda', 'BI' => 'Burundi', 'TZ' => 'Tanzania', 'MW' => 'Malawi',
                    'ZM' => 'Zambia', 'ZW' => 'Zimbabwe', 'MZ' => 'Mozambique', 'MG' => 'Madagascar',
                    'RE' => 'Reunion', 'YT' => 'Mayotte', 'SC' => 'Seychelles', 'KM' => 'Comoros',
                    'MU' => 'Mauritius', 'SH' => 'Saint Helena', 'CV' => 'Cape Verde',
                    'GW' => 'Guinea-Bissau', 'GN' => 'Guinea', 'SL' => 'Sierra Leone',
                    'LR' => 'Liberia', 'CI' => 'Ivory Coast', 'BF' => 'Burkina Faso',
                    'GH' => 'Ghana', 'TG' => 'Togo', 'BJ' => 'Benin', 'NG' => 'Nigeria',
                    'NE' => 'Niger', 'ML' => 'Mali', 'MR' => 'Mauritania', 'SN' => 'Senegal',
                    'GM' => 'Gambia', 'GW' => 'Guinea-Bissau', 'GN' => 'Guinea',
                    'SL' => 'Sierra Leone', 'LR' => 'Liberia', 'CI' => 'Ivory Coast',
                    'BF' => 'Burkina Faso', 'GH' => 'Ghana', 'TG' => 'Togo', 'BJ' => 'Benin',
                    'NG' => 'Nigeria', 'NE' => 'Niger', 'ML' => 'Mali', 'MR' => 'Mauritania',
                    'DZ' => 'Algeria', 'TN' => 'Tunisia', 'LY' => 'Libya', 'EG' => 'Egypt',
                    'SD' => 'Sudan', 'SS' => 'South Sudan', 'ET' => 'Ethiopia', 'SO' => 'Somalia',
                    'DJ' => 'Djibouti', 'ER' => 'Eritrea', 'CG' => 'Congo', 'CD' => 'DR Congo',
                    'GA' => 'Gabon', 'GQ' => 'Equatorial Guinea', 'ST' => 'Sao Tome and Principe',
                    'CM' => 'Cameroon', 'CF' => 'Central African Republic', 'TD' => 'Chad',
                    'LY' => 'Libya', 'TN' => 'Tunisia', 'DZ' => 'Algeria', 'MA' => 'Morocco',
                    'EH' => 'Western Sahara', 'MR' => 'Mauritania', 'SN' => 'Senegal',
                    'GM' => 'Gambia', 'GW' => 'Guinea-Bissau', 'GN' => 'Guinea',
                    'SL' => 'Sierra Leone', 'LR' => 'Liberia', 'CI' => 'Ivory Coast',
                    'BF' => 'Burkina Faso', 'ML' => 'Mali', 'NE' => 'Niger', 'TD' => 'Chad',
                    'SD' => 'Sudan', 'SS' => 'South Sudan', 'ET' => 'Ethiopia', 'SO' => 'Somalia',
                    'DJ' => 'Djibouti', 'ER' => 'Eritrea', 'CG' => 'Congo', 'CD' => 'DR Congo',
                    'GA' => 'Gabon', 'GQ' => 'Equatorial Guinea', 'ST' => 'Sao Tome and Principe',
                    'CM' => 'Cameroon', 'CF' => 'Central African Republic', 'TD' => 'Chad',
                    'LY' => 'Libya', 'TN' => 'Tunisia', 'DZ' => 'Algeria', 'MA' => 'Morocco',
                    'EH' => 'Western Sahara', 'MR' => 'Mauritania', 'SN' => 'Senegal',
                    'GM' => 'Gambia', 'GW' => 'Guinea-Bissau', 'GN' => 'Guinea',
                    'SL' => 'Sierra Leone', 'LR' => 'Liberia', 'CI' => 'Ivory Coast',
                    'BF' => 'Burkina Faso', 'ML' => 'Mali', 'NE' => 'Niger', 'TD' => 'Chad',
                    'SD' => 'Sudan', 'SS' => 'South Sudan', 'ET' => 'Ethiopia', 'SO' => 'Somalia',
                    'DJ' => 'Djibouti', 'ER' => 'Eritrea', 'CG' => 'Congo', 'CD' => 'DR Congo',
                    'GA' => 'Gabon', 'GQ' => 'Equatorial Guinea', 'ST' => 'Sao Tome and Principe',
                    'CM' => 'Cameroon', 'CF' => 'Central African Republic', 'TD' => 'Chad',
                    'LY' => 'Libya', 'TN' => 'Tunisia', 'DZ' => 'Algeria', 'MA' => 'Morocco',
                    'EH' => 'Western Sahara', 'MR' => 'Mauritania', 'SN' => 'Senegal',
                    'GM' => 'Gambia', 'GW' => 'Guinea-Bissau', 'GN' => 'Guinea',
                    'SL' => 'Sierra Leone', 'LR' => 'Liberia', 'CI' => 'Ivory Coast',
                    'BF' => 'Burkina Faso', 'ML' => 'Mali', 'NE' => 'Niger', 'TD' => 'Chad',
                    'SD' => 'Sudan', 'SS' => 'South Sudan', 'ET' => 'Ethiopia', 'SO' => 'Somalia',
                    'DJ' => 'Djibouti', 'ER' => 'Eritrea', 'CG' => 'Congo', 'CD' => 'DR Congo',
                    'GA' => 'Gabon', 'GQ' => 'Equatorial Guinea', 'ST' => 'Sao Tome and Principe',
                    'CM' => 'Cameroon', 'CF' => 'Central African Republic', 'TD' => 'Chad'
                ];
                @endphp
                
                @forelse(array_slice($countryData, 0, 8, true) as $countryCode => $count)
                @php
                    $total = array_sum($countryData);
                    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                    $flag = strtolower($countryCode);
                    $countryName = $countryNames[$countryCode] ?? $countryCode;
                @endphp
                <div class="flex items-center gap-3">
                    <img src="https://flagcdn.com/w40/{{ $flag }}.png" 
                         class="w-6 h-4 rounded object-cover" 
                         alt="{{ $countryCode }}"
                         onerror="this.style.display='none'">
                    <div class="flex-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">{{ $countryName }}</span>
                            <span class="text-gray-500">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No data yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Pages -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Top Pages</h3>
            <div class="space-y-2">
                @forelse(array_slice($pageData, 0, 10, true) as $page => $count)
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file text-gray-400"></i>
                        <span class="text-sm text-gray-700 truncate max-w-xs">{{ $page }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-800">{{ $count }} views</span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No data yet</p>
                @endforelse
            </div>
        </div>

        <!-- Devices -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Devices</h3>
            
            @php
                $deviceColors = ['desktop' => '#3b82f6', 'mobile' => '#10b981', 'tablet' => '#a855f7', 'other' => '#f59e0b'];
                $totalDevices = array_sum($deviceData);
            @endphp
            
            @if($totalDevices > 0)
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <!-- Simple CSS Pie Chart -->
                <div style="width: 150px; height: 150px; border-radius: 50%; position: relative; overflow: hidden;">
                    @php
                        $startAngle = 0;
                    @endphp
                    @foreach($deviceData as $device => $count)
                        @php
                            $percentage = ($count / $totalDevices) * 100;
                            $angle = ($count / $totalDevices) * 360;
                            $color = $deviceColors[$device] ?? '#9ca3af';
                        @endphp
                        <div style="position: absolute; width: 50%; height: 100%; right: 0; transform-origin: left center; transform: rotate({{ $startAngle }}deg); background: {{ $color }}; clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);"></div>
                        @php
                            $startAngle += $angle;
                        @endphp
                    @endforeach
                </div>
            </div>
            @endif
            
            <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                @foreach($deviceData as $device => $count)
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $deviceColors[$device] ?? '#9ca3af' }};"></div>
                    <span style="font-size: 13px; color: #4b5563; text-transform: capitalize;">{{ $device }} ({{ $count }})</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Back Link -->
    <div class="flex justify-between">
        <a href="{{ route('client.analytics.site.detail', $site->id) }}" 
           class="text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-code mr-2"></i>Get Tracking Code
        </a>
        <a href="{{ route('client.analytics.dashboard') }}" 
           class="text-gray-600 hover:text-gray-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Back to Websites
        </a>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chart.js loading...');
    
    // Check if Chart is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded! Loading fallback...');
        // Fallback: load from alternate CDN
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js';
        script.onload = function() {
            console.log('Chart.js loaded from fallback');
            initCharts();
        };
        script.onerror = function() {
            console.error('Failed to load Chart.js from all sources');
            document.getElementById('chartFallback').style.display = 'block';
        };
        document.head.appendChild(script);
    } else {
        console.log('Chart.js loaded successfully');
        initCharts();
    }
    
    function initCharts() {
        // Traffic Chart
        const ctx = document.getElementById('trafficChart');
        if (!ctx) {
            console.error('Canvas element not found!');
            return;
        }
        
        try {
            const labels = {!! json_encode($chartData->pluck('date')->map(fn($d) => $d->format('M d'))->toArray()) !!};
            const pageviews = {!! json_encode($chartData->pluck('pageviews')->toArray()) !!};
            const visitors = {!! json_encode($chartData->pluck('unique_visitors')->toArray()) !!};
            
            console.log('Chart labels:', labels);
            console.log('Chart pageviews:', pageviews);
            console.log('Chart visitors:', visitors);
            
            if (labels.length === 0) {
                console.warn('No chart data available');
                document.getElementById('chartFallback').style.display = 'block';
                return;
            }
            
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pageviews',
                        data: pageviews,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Unique Visitors',
                        data: visitors,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            console.log('Traffic chart created successfully');
            
        } catch (e) {
            console.error('Chart error:', e);
            document.getElementById('chartFallback').style.display = 'block';
        }
        
        // Device Chart
        const deviceCtx = document.getElementById('deviceChart');
        if (deviceCtx && {!! json_encode(count($deviceData) > 0) !!}) {
            try {
                new Chart(deviceCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(array_keys($deviceData)) !!},
                        datasets: [{
                            data: {!! json_encode(array_values($deviceData)) !!},
                            backgroundColor: ['#3b82f6', '#10b981', '#a855f7', '#f59e0b']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Device chart error:', e);
            }
        }
    }
});
</script>
@endsection
@endsection