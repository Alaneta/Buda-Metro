@extends('layouts.base_layout')

@section('css_custom_files')
    <link href="{{ asset('css/dropzone.min.css') }}" rel="stylesheet">
@stop

@section('css_custom_style')
    <style>
        #dropzone{
            padding:15px;
            border-style:dashed;
            border-color:#4F90FF;
            border-width:2px;
            background-color:#fdfbfb;
            color:#462a51;
        }

        #dropzone:hover {
            border-color: #1440FB;
        }

        .dropzone .dz-message .dz-button {
            font: caption;
        }

        .main{
            padding-top: 0px !important;
        }
    </style>
@stop

@section('content')
    <!-- ======= Hero Section ======= -->
    <section id="hero">
        <div class="hero-container" data-aos="zoom-in" data-aos-delay="100">
            <h1>Bienvenido a Buda Metro</h1>
            <h2>Calcule la ruta con menor cantidad de estaciones</h2>
            <a href="#guide" class="btn-get-started">Empezar</a>
        </div>
    </section><!-- End Hero Section -->

    <main id="main">

        <!-- ======= Guide Section ======= -->
        <section id="guide">
            <div class="container" data-aos="fade-up">
                <div class="row guide-container">

                    <div class="col-lg-6 content order-lg-1 order-2">
                        <h2 class="title">Guía para la carga de red</h2>
                        <p>
                            En la sección "CARGA DE RED METRO", adjunte un archivo en formato .CSV con las diferentes conexiones de la red de metro. Puede descargar un template de ejemplo aquí
                        </p>
                        <div style="margin-bottom: 20px;">
                            <a href="{{ route("network.download") }}"><button class="btn btn-secondary"><i class="fa fa-download"></i> Ejemplo</button></a>
                        </div>
                        <div class="icon-box" data-aos="fade-up" data-aos-delay="100">
                            <div class="icon"><i class="bi bi-check"></i></div>
                            <h4 class="title"><a href="">Donde ubicar la estación y su conexión</a></h4>
                            <p class="description">En cualquier parte del archivo mientras la estación y su conexión se mantengan una al lado de la otra. Ejemplo: En la fila-columna A-1 se pone la estación X y en la fila-columna B-1 se pone la estación Y con la que conecta</p>
                        </div>

                        <div class="icon-box" data-aos="fade-up" data-aos-delay="200">
                            <div class="icon"><i class="bi bi-check"></i></div>
                            <h4 class="title"><a href="">No hace falta cargar el "ida y vuelta"</a></h4>
                            <p class="description">Si se carga la conexión de la estación X con la estación Y, no hace falta cargar la conexión de la estación Y con la X</p>
                        </div>

                        <div class="icon-box" data-aos="fade-up" data-aos-delay="300">
                            <div class="icon"><i class="bi bi-check"></i></div>
                            <h4 class="title"><a href="">Estaciones con colores</a></h4>
                            <p class="description">Para indicar que una estación tiene color, debe escribirse con el formato "Nombre de estación-Color". Ejemplo: G-Verde</p>
                        </div>

                        <div class="icon-box" data-aos="fade-up" data-aos-delay="300">
                            <div class="icon"><i class="bi bi-check"></i></div>
                            <h4 class="title"><a href="">Calcular ruta</a></h4>
                            <p class="description">En la sección "REDES DE METRO CARGADAS", elija una red y oprima en el botón <i class="fa-solid fa-calculator"></i>. Luego elija la estación inicial, final, color de tren y oprima en "Calcular"</p>
                        </div>

                    </div>

                    <div class="col-lg-6 background order-lg-2 order-1" data-aos="fade-left" data-aos-delay="100"></div>
                </div>

            </div>
        </section><!-- End Guide Section -->

        <!-- ======= Upload Section ======= -->
        <section id="uploadNetwork">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h3 class="section-title">Carga de Red Metro</h3>
                    <p class="section-description">Aquí adjunte el archvio en formato .CSV</p>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="{{ route('save') }}" type="multipart/form-data">
                            @csrf
                            <div class="dropzone mt-10" id="dropzone"></div>
                        </form>
                        <div class="text-center mt-10">
                            <button type="submit" class="btn btn-primary" id="uploadFile">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </section><!-- End Upload Section -->

        <!-- ======= Networks List Section ======= -->
        <section id="networksList">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h3 class="section-title">Redes de metro cargadas</h3>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="recuadro">
                            <div class="recuadro-content table-responsive">
                                <table id="table_id" class="display" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Fecha de creación</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($networks as $network)
                                        <tr>
                                            <td id="id">{{ $network->id }}</td>
                                            <td>{{ $network->name }}</td>
                                            <td>{{ $network->created_at }}</td>
                                            <td><a href="{{ route("network.showCalculateRoute", $network->id) }}"><button type="button" title="Calcular ruta" class="btn btn-primary btn-sm edit"><i class="fa-solid fa-calculator"></i></button></a></td>
                                            <td><button type="button" title="Borrar" class="btn btn-danger btn-sm delete"><i class="far fa-trash-alt"></i></button></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section><!-- End Upload Section -->

    </main><!-- End #main -->
@stop

@section('js_custom_files')
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop

@push('scripts')
    <script>
        Dropzone.autoDiscover = false;

        var myDropzone = new Dropzone(".dropzone", {
            autoProcessQueue: false,
            uploadMultiple: true,
            url: '{{ route('save') }}',
            maxFiles: 1,
            maxFilesize: 4,
            acceptedFiles: ".csv",
            addRemoveLinks: true,
            timeout: 50000,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            init: function() {
                this.on('sendingmultiple', function(data, xhr, formData) {
                    // formData.append('initialStation', $('#initialStation').val());
                    // formData.append('finalStation', $('#finalStation').val());
                    // formData.append('trainColor', $('#trainColor').val());
                });
                this.on('addedfile', file => {
                });
                this.on('removedfile', file => {
                });
            },
            success: function(data, xhr, formData) {
                console.log('success');
                Swal.fire(
                    'Red de metro guardados!',
                    'Los datos de la red de metro han sido guardados.',
                    'success'
                ).then((result) => {
                    this.removeAllFiles();
                    window.location.reload();
                });
            },
            error: function(data, xhr, formData) {
                console.log('error');
                var errorList = '';
                if (xhr.errors) {
                    $.each(xhr.errors, function( index, value ) {
                        errorList += '<li>' + value + '</li>'
                    });
                    errorList = errorList.replace('undefined', '');
                }
                Swal.fire({
                    title: 'Se produjo un error',
                    icon: 'error',
                    html:
                        '<ol>' + errorList + '</ol>'
                }).then((result) => {
                    this.removeAllFiles();
                });
            }
        });

        $(document).ready(function () {

            var dataTable = $('#table_id').DataTable({
                "order": [[ 0, "asc" ]],
                "columnDefs": [
                    {
                        "targets": [ 0 ],
                        "visible": true,
                        "searchable": false
                    },
                    {
                        "targets": [ 3, 4 ],
                        "visible": true,
                        "searchable": false,
                        "orderable": false,
                        "width": "1%"
                    },
                ]
            });

            $('#uploadFile').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                myDropzone.processQueue();
            });

            $("button.delete").each(function(){
                $(this).click(function(event){
                    event.preventDefault();
                    let button = $(this);
                    const id = button.parents().closest("tr").find("#id").text();
                    var url = "{{ route('network.delete', ':id') }}";
                    url = url.replace(':id', id);
                    Swal.fire({
                        title: 'Está a punto de eliminar la red de metro',
                        text: 'No podrá revertir este cambio',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                success: function(result) {
                                    button.parents().closest("tr").remove();
                                    Swal.fire(
                                        'Red eliminada!',
                                        'La red ha sido eliminada.',
                                        'success'
                                    ).then((result) => {
                                        window.location.reload();
                                    });
                                }
                            });
                        }
                    })
                })
            })
        });
    </script>
@endpush
