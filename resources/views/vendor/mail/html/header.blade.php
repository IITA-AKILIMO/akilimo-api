@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}">
<img src="{{ rtrim(config('app.url'), '/') }}/images/akilimo_logo_colored.png" class="logo" alt="{{ $slot }}" height="44">
</a>
</td>
</tr>
