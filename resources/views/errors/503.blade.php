@extends('errors::minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', 'Cerrado por mantenimiento')
@section('hint', 'Estamos puliendo la máquina de espresso. Volvemos enseguida.')
