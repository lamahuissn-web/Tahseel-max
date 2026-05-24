<!--end::Modals-->
<!--begin::Javascript-->
<script>
    var hostUrl = "{{asset('assets')}}/";
</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
<!--end::Global Javascript Bundle-->
<!--begin::Vendors Javascript(used for this page only)-->
<script src="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js')}}"></script>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<script src="https://cdn.amcharts.com/lib/5/map.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/continentsLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/usaLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js"></script>
<script src="{{asset('assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
<!--end::Vendors Javascript-->
<!--begin::Custom Javascript(used for this page only)-->
<script src="{{asset('assets/js/widgets.bundle.js')}}"></script>
<script src="{{asset('assets/js/custom/apps/chat/chat.js')}}"></script>
<script src="{{asset('assets/js/custom/utilities/modals/upgrade-plan.js')}}"></script>
<script src="{{asset('assets/js/custom/utilities/modals/create-campaign.js')}}"></script>
<script src="{{asset('assets/js/custom/utilities/modals/users-search.js')}}"></script>
<!--end::Custom Javascript-->
<!--end::Javascript-->

<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })

    var datatable;
    var globalDataTableConfig = {
        scrollX: true,
        select: false,
        // dom: 'lfrtip',
        dom: "<'row mb-2'" +
            "<'col-sm-6 d-flex align-items-center justify-conten-start dt-toolbar'l>" +
            "<'col-sm-6 d-flex align-items-center justify-content-end dt-toolbar'f>" +
            ">" +

            "<'table-responsive'tr>" +

            "<'row'" +
            "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
            "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
            ">"
        // Add any other common configurations here
    };
     
    if(app()->getLocale() == 'ar')
    globalDataTableConfig.language = {
        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
    };
    

    function bulidDatatable(table_id, pageSpecificConfig) {

        // Merge global and page-specific configurations
        var finalConfig = $.extend({}, globalDataTableConfig, pageSpecificConfig);

        // Initialize DataTables with the merged configuration
        datatable = $('#' + table_id).DataTable(finalConfig);
        return datatable;
    }
</script>

<script>
    $(document).ready(function() {
        if ($('#table_10').length) {
            $('#table_10').DataTable({
                processing: true,
                serverSide: false,
                order: [],
                columnDefs: [{
                        targets: [1, -1],
                        orderable: false,
                    },
                    {
                        targets: [1],
                        createdCell: function(td) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': '#6610f2',
                                'vertical-align': 'middle',
                            });
                        }
                    }
                ],
                dom: '<"row align-items-center"<"col-md-3"l><"col-md-6"f><"col-md-3"B>>rt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
                buttons: ['excel', 'copy'],
                language: {
                    url: "{{ asset('assets/Arabic.json') }}"
                }
            });
        }
    });
</script>

@yield('js')

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').then(function(registration) {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }
</script>