<x-main-layout>
    <x-slot:title>
        LHG SOW Tool: Next-Level AI SOW Generator
    </x-slot>
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
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Prompt/Instructions</label>
                                <textarea name="prompt" class="form-control" rows="3" placeholder=""> {{ $sow->prompt }} </textarea>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Your Conversation/Project Description</label>
                                <textarea name="description" class="form-control" rows="10" placeholder=""> {{ $sow->description }} </textarea>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                {{-- <button type="submit" class="btn btn-block btn-dark btn-sm">Submit</button> --}}
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
                     @markdown($sow->sow)
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</x-main-layout>
