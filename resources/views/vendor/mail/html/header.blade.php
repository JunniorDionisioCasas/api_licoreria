<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === config('app.name'))
<img src="https://licoreriasansebastian.com/images/logo/logo_black_and_white_150px.PNG" class="logo" alt="San Sebastián">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
