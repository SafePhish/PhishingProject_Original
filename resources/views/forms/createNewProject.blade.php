@extends('masters.basemaster')
@section('title')
    Create New Project
@stop
@section('csrf_token')
    <meta name="_token" content="{{ csrf_token() }}" />
@stop
@section('scripts')
    <script type="text/javascript" src="/js/createNewProject.js"></script>
@stop
@section('formcss')
    <link rel="stylesheet" type="text/css" href="/css/baseformstyles.css" />
@stop
@section('projectsClassDefault')
    activeNavButton tempActiveNavButton
@stop
@section('bodyContent')
    {!! Form::open(array('action'=>'PhishingController@createNewProject')) !!}
    <p>{!! Form::label('projectNameText','Project Name: ') !!}
        {!! Form::text('projectNameText',null,array('name'=>'projectNameText')) !!}</p>
    <p>{!! Form::label('projectAssigneeText','Project Assignee: ') !!}
        {!! Form::text('projectAssigneeText',null,array('name'=>'projectAssigneeText')) !!}</p>
    {!! Form::submit('Submit',array('id'=>'submitButton')) !!}
    {!! Form::close() !!}
@stop