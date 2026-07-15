<h1>週間の服薬レポート</h1>

<div style="margin-bottom: 20px;">
    <a href="{{ route('patients.index') }}">← お薬一覧に戻る</a>
</div>

@foreach($patients as $patient)
    <div style="margin-bottom: 30px; border: 1px solid #ccc; padding: 15px; border-radius: 8px;">
        <h3>{{ $patient->name }} さんの記録</h3>
        
        <table border="1" style="width: 100%; border-collapse: collapse; text-align: center; font-size: 12px;">
            <tr style="background-color: #f0f0f0;">
                <th>お薬名</th>
                @foreach($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('m/d') }}</th>
                @endforeach
            </tr>
            
            @foreach($patient->medicines as $medicine)
                <tr>
                    <td style="padding: 8px; background-color: #fafafa;"><strong>{{ $medicine->medicine_name }}</strong></td>
                    @foreach($dates as $date)
                        @php
                            // その日の服用記録を探す
                            $adherence = $medicine->adherences->where('taken_date', $date)->first();
                        @endphp
                        <td style="padding: 8px; vertical-align: top;">
                            @if($adherence)
                                <span style="color: #4CAF50; font-weight: bold;">✅</span>
                                @if($adherence->note)
                                    <div style="margin-top: 5px; font-size: 10px; color: #666; background: #fffde7; padding: 2px; border-radius: 3px; border: 1px solid #ffe082;">
                                        {{ $adherence->note }}
                                    </div>
                                @endif
                            @else
                                <span style="color: #ddd;">ー</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>
@endforeach