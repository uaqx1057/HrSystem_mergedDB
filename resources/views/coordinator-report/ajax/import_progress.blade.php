@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.coordinatorReport'),
    'processRoute' => route('coordinator-report.import.process'),
    'backRoute' => route('coordinator-report.index'),
    'backButtonText' => __('app.backToEmployees'),
])
