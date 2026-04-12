<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>SIPENA | Checklist AMPUH PN Bengkulu</title>


    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicons/favicon.ico">
    <link rel="manifest" href="assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <script src="assets/js/config.js"></script>
    <script src="vendors/simplebar/simplebar.min.js"></script>


    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link href="vendors/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
    <link href="vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link href="assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
    <link href="assets/css/theme.css" rel="stylesheet" id="style-default">
    <link href="assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
    <link href="assets/css/user.css" rel="stylesheet" id="user-style-default">
    <script>
      var isRTL = JSON.parse(localStorage.getItem('isRTL'));
      if (isRTL) {
        var linkDefault = document.getElementById('style-default');
        var userLinkDefault = document.getElementById('user-style-default');
        linkDefault.setAttribute('disabled', true);
        userLinkDefault.setAttribute('disabled', true);
        document.querySelector('html').setAttribute('dir', 'rtl');
      } else {
        var linkRTL = document.getElementById('style-rtl');
        var userLinkRTL = document.getElementById('user-style-rtl');
        linkRTL.setAttribute('disabled', true);
        userLinkRTL.setAttribute('disabled', true);
      }
    </script>
  </head>


  <body>
    <main class="main" id="top">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <section class="py-3 bg-light shadow-sm" style="background-color: rgb(249, 250, 253) !important;">

        <div class="container">
          <div class="row flex-center">
            <div class="col-lg-12">
              <span class="text-white dark__text-white" style="color: #2c7be5 !important;text-align: center;">
                <h5>Checklist:</h5>
                <h3 style="font-weight: 800;color: #2c7be5 !important;">AMPUH (sertifikAsi Mutu Pengadilan Unggul dan Tangguh) </h3>
              </span>
            </div>
          </div>
        </div>
        <!-- end of .container-->
  
      </section>
      <section style="padding-top:10px !important">

        <div class="container" style="padding:0;margin:0;max-width:100% !important;font-size:0.7vw;">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tr>
                                    <td>No</td>
                                    <td>Checklist</td>
                                    <td>Subchecklist</td>
                                    <td>File</td>
                                </tr>
                                @foreach($data as $list)
                                    @php
                                        $jlh_lvl1=count($list['lvl1']);            
                                    @endphp
                                    <tr>
                                        <td rowspan="{!! $jlh_lvl1 +1 !!}">{!! $no !!}</td>
                                        <td rowspan="{!! $jlh_lvl1 +1 !!}">{!! $list['parent_name'] !!}</td>
                                        @for($x=0;$x<$jlh_lvl1;$x++)
                                            @php
                                                $jlh_lvl2=0;
                                                if(isset($list['lvl1'][$x]['lvl2'])){
                                                    $jlh_lvl2=count($list['lvl1'][$x]['lvl2']);
                                                }
                                            @endphp
                                            <tr>
                                                <td>{!! $list['lvl1'][$x]['nama_lvl1'] !!}</td>
                                                <td>
                                                    <ol>
                                                        @for($y=0;$y<$jlh_lvl2;$y++)
                                                            <li class='bold' style="list-style-type:none;font-weight:800">
                                                                {!! $list['lvl1'][$x]['lvl2'][$y]['nama_lvl2'] !!} 
                                                                    @if(!isset($list['lvl1'][$x]['lvl2'][$y]['lvl3']))
                                                                        ({!! is_null($list['lvl1'][$x]['lvl2'][$y]['judul']) ? "<span style='color:red;'>Belum diupload</span>" : "" !!})
                                                                    @else
                                                                        @php $jlh_lvl3=count($list['lvl1'][$x]['lvl2'][$y]['lvl3']) @endphp
                                                                        @for($lvl3=0;$lvl3<$jlh_lvl3;$lvl3++)
                                                                            <ul>
                                                                                <li>
                                                                                    {!! $list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['nama_lvl3'] !!} - ({!! $list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['judul'] !!})
                                                                                    @php $jlh_edoc=count($list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['edoc']) @endphp
                                                                                    @if($jlh_edoc > 0)
                                                                                        <ul>
                                                                                            @for($edoc=0;$edoc<$jlh_edoc;$edoc++)
                                                                                                <ul>
                                                                                                    <li>{!! $list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['edoc'][$edoc]['filename'] !!}</li>
                                                                                                    @php $jlh_file=count($list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['edoc'][$edoc]['file']) @endphp
                                                                                                    @for($file_index=0;$file_index<$jlh_file;$file_index++)
                                                                                                        <li>
                                                                                                            <a target='_blank' href="https://drive.google.com/file/d/{!! $list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['edoc'][$edoc]['file'][$file_index]['edoc'] !!}"> 
                                                                                                                {!! $list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['edoc'][$edoc]['file'][$file_index]['timeline'] !!}  
                                                                                                                {!! is_null($list['lvl1'][$x]['lvl2'][$y]['lvl3'][$lvl3]['edoc'][$edoc]['file'][$file_index]['edoc']) ? "<span style='color:red'> - Belum diupload</span>" : "" !!}
                                                                                                            </a>
                                                                                                        </li>
                                                                                                    @endfor
                                                                                                </ul>
                                                                                            @endfor
                                                                                        </ul>
                                                                                    @endif
                                                                                </li>
                                                                            </ul>
                                                                        @endfor
                                                                    @endif
                                                                @php
                                                                    if(isset($list['lvl1'][$x]['lvl2'][$y]['edoc'])){
                                                                        $jlh_edoc=count($list['lvl1'][$x]['lvl2'][$y]['edoc']);
                                                                        if($jlh_edoc > 0){
                                                                            for($z=0;$z<$jlh_edoc;$z++){
                                                                                echo "<ul>
                                                                                    <li>".$list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['filename']."</li>
                                                                                    ";
                                                                                    if(isset($list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['file'])){
                                                                                        $jlh_file=count($list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['file']);
                                                                                        for($z1=0;$z1<$jlh_file;$z1++){
                                                                                            if($list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['file'][$z1]['max_fill_at'] < date(now())){
                                                                                            echo "
                                                                                                <ul>
                                                                                                    <li>
                                                                                                        <a target='_blank' href='https://drive.google.com/file/d/".$list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['file'][$z1]['edoc']."'>"
                                                                                                            .$list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['file'][$z1]['timeline']." ";

                                                                                                            if(is_null($list['lvl1'][$x]['lvl2'][$y]['edoc'][$z]['file'][$z1]['edoc'])){
                                                                                                                echo "<span style='color:red;'>(Belum diupload)</span>";
                                                                                                            } 
                                                                                                        echo"</a></li>
                                                                                                </ul>
                                                                                            ";
                                                                                        }
                                                                                        }
                                                                                        
                                                                                    }else{
                                                                                        echo "<span style='color:red;'>Belum di setting</span>";
                                                                                    }
                                                                                    
                                                                                echo "</ul>";
                                                                            }
                                                                        }
                                                                    }else{

                                                                    }

                                                                @endphp
                                                            </li>
                                                            <br />
                                                        @endfor
                                                    </ol>
                                                </td>
                                            </tr>
                                        @endfor
                                        
                                    </tr>
                                
                                    @php $no++;@endphp
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->




      <!-- ============================================-->
      <!-- <section> begin ============================-->
      


    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->
    <script src="vendors/popper/popper.min.js"></script>
    <script src="vendors/bootstrap/bootstrap.min.js"></script>
    <script src="vendors/anchorjs/anchor.min.js"></script>
    <script src="vendors/is/is.min.js"></script>
    <script src="vendors/swiper/swiper-bundle.min.js"> </script>
    <script src="vendors/typed.js/typed.js"></script>
    <script src="vendors/fontawesome/all.min.js"></script>
    <script src="vendors/lodash/lodash.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
    <script src="vendors/list.js/list.min.js"></script>
    <script src="assets/js/theme.js"></script>

  </body>

</html>












