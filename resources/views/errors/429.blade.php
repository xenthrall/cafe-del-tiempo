@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', 'Vas muy rápido pidiendo')
@section('hint', 'Un café a la vez, por favor. Respira y vuelve a intentarlo en un momento.')
