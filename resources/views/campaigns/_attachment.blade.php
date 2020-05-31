<link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/js/plugins/dropzone/css/dropzone.css') }}" />

  <div class="image_upload_div">
    <form action="{{url('/')}}/filemanager/upload_attachment.php" class="dropzone">
       <input name="path" value="../campaign_attachment/{{$campaign->uid}}/" type="hidden">
       <input name="path_thumb" value="../thumbs/{{$campaign->uid}}/" type="hidden">
    </form>
  </div>
  <div class="attachments_pnl">
    <?php
      $path_campaign=public_path().'/campaign_attachment'.'/'.$campaign->uid;
      if (is_dir($path_campaign)) {
          echo "<h3 class='mt-40'>" . trans('messages.campaign.attached_files') . " <br/></h3>";
          $ffs = scandir($path_campaign);

          unset($ffs[array_search('.', $ffs, true)]);
          unset($ffs[array_search('..', $ffs, true)]);

          // prevent empty ordered elements
          if (count($ffs) < 1) {
              return;
          }

          echo '<ul>';
          foreach ($ffs as $k=>$ff) {
                $fileszie = formatSizeUnits(filesize( $path_campaign . '/' . $ff));
              echo '<li id="delete_'.$k.'"><i class="lnr lnr-file-empty icon-file"></i><span><name>'.$ff.'</name><size>'.trans('messages.campaign.attachment.file_size_is', ['size' => $fileszie]).'</size></span>'; ?> 
            <form action="{{url('/')}}/filemanager/force_download_attachment.php" method="post" class="download-form" id="{{$k}}">
              <input name="path" value="{{$campaign->uid}}{{'/'}}" type="hidden">
              <input class="name_download" name="name" value="{{$ff}}" type="hidden">
<span>
              <a title="" class="tip-right" href="javascript:void('')" onclick="$('#{{$k}}').submit();" data-original-title="Download">{{ trans('messages.download') }}</a>
|
             <a
                file-delete-confirm="{{url('/')}}/filemanager/execute_attachment.php?_token={{ csrf_token() }}&action=delete_file&file={{$ff}}&path={{$campaign->uid}}{{'/'}}"
                href="{{url('/')}}/filemanager/execute_attachment.php?_token={{ csrf_token() }}&action=delete_file&file={{$ff}}&path={{$campaign->uid}}{{'/'}}"
                title="Delete"
                class=""
                data-del="delete_{{$k}}"
            >
                 {{ trans('messages.remove') }}
              </a>
</span>
            </form>
            <?php
                  if (is_dir($path_campaign.'/'.$ff)) {
                      listFolderFiles($path_campaign.'/'.$ff);
                  }
              echo '</li>';
          }
          echo '</ol>';
      }
    ?>

  </div>
