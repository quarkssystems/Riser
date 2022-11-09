@if($postComments && $postComments->count() > 0)
    @foreach ($postComments as $comment)
        <div class="row ml-{{ $level ?? '1' }} mb-3 cmt-box">
            <div class="col-md-12">
                <img class="user-image img-circle" src="{{ $comment->user->profile_picture_url }}" alt="" />
                <small><strong>{{ $comment->user->full_name }}</strong></small>
            </div>
            <div class="col-md-12">{{ $comment->comments ?? '' }}</div>
        </div>
        @if($comment->childrenRecursive)
            @php
                $level++;
            @endphp
            @include('posts.recursive', ['postComments' => $comment->childrenRecursive, 'level' => $level])
        @endif
    @endforeach
@endif

@section('css')
        <style>
            .cmt-box {
                background-color: #ededed;
                padding: 10px;
                border-radius: 5px;
                border-left: 3px solid #ccc; 
            }

            .cmt-box img {
                max-width: 25px;
            }
        </style>
@endsection