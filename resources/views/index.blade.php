<x-main-layout>
    <x-slot:title>
        AIContentPro: Next-Level AI Content Generation with Laravel
    </x-slot>
<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
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
                                <option>English</option>
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
                            <textarea class="form-control" rows="3" placeholder=""></textarea>
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
                            <button type="button" class="btn btn-block btn-dark btn-sm">Generate</button>
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
                <h4 class="card-title">বাংলা ভাষায় চ্যাটজিপিটির (ChatGPT) ব্যবহারের কিছু সম্ভাবনামূলক উদাহরণ নিচে দেয়া হলো:</h4> <br>
                <p class="card-text">১. বাংলা ভাষার শেখার জন্য: আমি শব্দের অর্থ, পরিভাষা এবং ব্যবহারের উদাহরণ সহ বাংলা ভাষা শেখাতে সহায়তা করতে পারি।</p>
                <p class="card-text">২. গ্রাহক সেবা: আমি চ্যাটবটের সাথে সমন্বয় করে কোম্পানিসমূহে বাংলায় গ্রাহক সেবা প্রদান করতে পারি। এটি বাংলাদেশে অথবা বাংলা ভাষা ব্যবহার করে কাস্টমারদের জন্য ব্যবসার জন্য উপযোগী।</p>
                <p class="card-text">২. গ্রাহক সেবা: আমি চ্যাটবটের সাথে সমন্বয় করে কোম্পানিসমূহে বাংলায় গ্রাহক সেবা প্রদান করতে পারি। এটি বাংলাদেশে অথবা বাংলা ভাষা ব্যবহার করে কাস্টমারদের জন্য ব্যবসার জন্য উপযোগী।</p>
                <p class="card-text">৩. কন্টেন্ট তৈরি: আমি বাংলা ভাষার কন্টেন্ট তৈরি করতে সহায়তা করতে পারি যেমন বাক্য রচনার জন্য শব্দার্থ পরামর্শ দেয়া, বানানের জন্য প্রস্তাব করা।</p>
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
</x-main-layout>
