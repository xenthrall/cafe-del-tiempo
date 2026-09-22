@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', 'Necesitas identificarte antes de servirte')
@section('hint', '¿Café gratis? Ni que fuera domingo. Inicia sesión para continuar.')
