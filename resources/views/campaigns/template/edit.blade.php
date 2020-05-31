<!doctype html>
<html>
  <head>
    <title>{{ trans('messages.campaign.edit_template') }} - {{ $campaign->name }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    @include('layouts._favicon')
    
    <link href="{{ URL::asset('builder/builder.css') }}" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="{{ URL::asset('builder/builder.js') }}"></script>
    
    <script>
        var CSRF_TOKEN = "{{ csrf_token() }}";
        var editor;
        
        var templates = {!! json_encode($templates) !!};
        
        $( document ).ready(function() {
            editor = new Editor({
                url: '{{ action('CampaignController@templateContent', $campaign->uid) }}',
                backCallback: function() {
                  parent.$('.full-iframe-popup').fadeOut();
                  parent.$('body').removeClass('overflow-hidden');

                  if (typeof(parent.builderSelectPopup) != 'undefined') {
                    parent.builderSelectPopup.hide();
                  }
                },
                uploadAssetUrl: '{{ action('CampaignController@templateAsset', $campaign->uid) }}',
                uploadAssetMethod: 'POST',
                saveUrl: '{{ action('CampaignController@templateEdit', $campaign->uid) }}',
                saveMethod: 'POST',
                tags: {!! json_encode(Acelle\Model\Template::builderTags((isset($list) ? $list : null))) !!},
                root: '{{ URL::asset('builder') }}/',
                templates: templates,
                logo: '{{ URL::asset('images/logo_light_builder.png') }}',
                backgrounds: [
                    '{{ url('/images/backgrounds/images1.jpg') }}',
                    '{{ url('/images/backgrounds/images2.jpg') }}',
                    '{{ url('/images/backgrounds/images3.jpg') }}',
                    '{{ url('/images/backgrounds/images4.png') }}',
                    '{{ url('/images/backgrounds/images5.jpg') }}',
                    '{{ url('/images/backgrounds/images6.jpg') }}',
                    '{{ url('/images/backgrounds/images9.jpg') }}',
                    '{{ url('/images/backgrounds/images11.jpg') }}',
                    '{{ url('/images/backgrounds/images12.jpg') }}',
                    '{{ url('/images/backgrounds/images13.jpg') }}',
                    '{{ url('/images/backgrounds/images14.jpg') }}',
                    '{{ url('/images/backgrounds/images15.jpg') }}',
                    '{{ url('/images/backgrounds/images16.jpg') }}',
                    '{{ url('/images/backgrounds/images17.png') }}',
                ]
            });
          
            editor.init();
        });
    </script>
  </head>
  <body>
  </body>
</html>