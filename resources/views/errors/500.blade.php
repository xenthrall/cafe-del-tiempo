@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', 'Se nos quemó el café')
@section('hint', 'Algo se rompió en la cocina. Ya estamos limpiando el desastre.')
