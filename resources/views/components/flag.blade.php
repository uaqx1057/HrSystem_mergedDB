@php
	$countryIso = null;
	$countryTitle = null;

	if (is_object($country) && isset($country->iso)) {
		$countryIso = $country->iso;
		$countryTitle = $country->nicename ?? $country->name ?? $country->iso;
	}
@endphp

@if ($countryIso)
	<img src="{{ asset('flags/4x3/' . strtolower($countryIso) . '.svg') }}" class="w-15 ml-" alt="{{ $countryIso }}" title="{{ $countryTitle }}" data-toggle="tooltip">
@elseif (!empty($country) && is_string($country))
	<span class="f-12 {{ isset($textColor) ? $textColor : 'text-dark-grey' }}">{{ $country }}</span>
@endif
