<x-main-layout>
    <x-slot:title>
        LHG SOW Tool: Next-Level AI SOW Generator
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    @endpush
<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">ChatGPT GPT-4 AI Model</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-4">
            <div class="card">
              <div class="card-header">
                <h5 class="m-0">Generate SOW</h5>
              </div>
              <div class="card-body">
                <form method="post" action="{{ route('generate.sow') }}">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Prompt/Instructions</label>
                                <textarea name="prompt" class="form-control" rows="3" placeholder="">Consider you are an experienced system analyst. Now Please help me with creating an Scope of Work Based on following Conversation -  </textarea>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Your Conversation/Project Description</label>
                                <textarea name="description" class="form-control" rows="10" placeholder=""></textarea>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-dark btn-sm">Submit</button>
                            </div>
                        </div>

                    </div>
                </form>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h5 class="m-0">Result</h5>
              </div>
              <div class="card-body">
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
    @push('scripts')
      <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
      <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
      <script src="{{ asset('libraby/API.js') }}"></script>

      <script>
        function generateContent(e){
          e.preventDefault();
          showPreloader();
          $("#generateButton").attr("disabled", true);
          var requestBody = {
            prompt: $('#prompt').val()
          };

          API.post(API.baseUrl + "/completion", requestBody)
          .then(data => {
            if(data.code == 200){
              toastr.success(data.message, 'Successful');
              $("#contentResult").html(data.data.text);

            }
            else{
              toastr.error(data.message, 'Error');
            }

            $("#generateButton").attr("disabled", false);
            hidePreloader();
          })
          .catch(error => {
            console.error(error)
          });

        }
      </script>
    @endpush
</x-main-layout>
