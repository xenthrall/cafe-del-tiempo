@extends('errors::minimal')

@section('title', __('Payment Required'))
@section('code', '402')
@section('message', 'Cuenta pendiente en la mesa')
@section('hint', 'Parece que esta mesa todavía no ha pagado la cuenta.')
