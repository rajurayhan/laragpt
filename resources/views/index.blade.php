<x-main-layout> 
    <x-slot:title>
        AIContentPro: Next-Level AI Content Generation with Laravel
    </x-slot>
    
    @push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
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
                <h4 class="card-title">Software Development Life Cycle (SDLC) is a structured process used in software development to design, develop, test, and deploy software. It consists of several phases, each with its own objectives, tasks, and deliverables. The SDLC is typically composed of six main phases, including:</h4>
                <p class="card-text">
                    <ol>
                        <li>
                            <strong>Requirements Gathering</strong>: In this phase, the project team interacts with the client or the end-user to determine their needs and requirements for the software. The requirements are documented, and a functional specification document is created.
                        </li>
                        <li>
                            <strong>Design</strong>: In this phase, the development team designs the architecture of the software, including the database structure, user interface design, and system components.
                        </li>
                        <li>
                            <strong>Implementation</strong>: In this phase, the development team starts building the software based on the design documents. They write code, integrate system components, and create the software's functionality.
                        </li>
                        <li>
                            <strong>Testing</strong>: In this phase, the development team tests the software to ensure that it is working correctly and meets the client's requirements. They test for bugs, errors, and defects in the software.
                        </li>
                        <li>
                            <strong>Deployment</strong>: In this phase, the software is deployed to the production environment. The deployment process involves installing the software on the client's system or on a server.
                        </li>
                        <li>
                            <strong>Maintenance</strong>: In this phase, the development team provides ongoing support and maintenance to the software, including bug fixes, upgrades, and enhancements.
                        </li>
                    </ol>
                </p>
                <p class="card-text">
                    The SDLC is a continuous process that enables the development team to create high-quality software that meets the client's requirements. It provides a framework for the development team to follow, ensuring that the software is developed efficiently, and that it meets the project's objectives.
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
      <script src="{{ asset('libraby/API.js') }}"></script> 
        
      <script>
        $(document).ready(function(){
          // Call the API
          // API.get('https://jsonplaceholder.typicode.com/posts/1')
          //   .then(data => {
          //     console.log(data)
          //   })
          //   .catch(error => {
          //     console.error(error)
          //   });
          });

          function generateContent(){
            alert(API.baseUrl);
            var requestBody = {
              prompt: $('#prompt').val()
            };

          }
      </script>
    @endpush 
</x-main-layout>
