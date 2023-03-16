<x-main-layout> 
    <x-slot:title>
        AIContentPro: Next-Level AI Content Generation with Laravel
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
            <h1 class="m-0">ChatGPT GPT-3.5-turbo AI Model</h1>
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
                <h5 class="m-0">Generate content</h5>
              </div>
              <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Language</label>
                            <select class="form-control">
                                <option>Bangla</option>
                                <option selected>English</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Max Result Length</label>
                            <input type="text" class="form-control" placeholder="200" >
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>What is your content about?</label>
                            <textarea class="form-control" rows="3" placeholder="Explain Software Development Life Cycle" id="prompt"> </textarea>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Creativity</label>
                            <select class="form-control">
                                <option>High</option>
                                <option>Low</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Number of results</label>
                            <select class="form-control">
                                <option>1</option>
                                <option>2</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-block btn-dark btn-sm" onclick="generateContent()">Generate</button>
                        </div>
                    </div>

                </div>
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
                <p class="card-text" id="contentResult">
                </p> 
                {{-- <a href="#" class="btn btn-primary">Go somewhere</a> --}}
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
        $(document).ready(function(){ 
          });

          function generateContent(){ 
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
            })
            .catch(error => {
              console.error(error) 
            });

          }

          function showErrorOrSuccess(data){
            // console.log(data);
            
          }
      </script>
    @endpush 
</x-main-layout>
