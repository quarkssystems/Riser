<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Pusher\Pusher;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\ChatNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ChatController extends Controller
{
    /**
     * Create a user investment detail service variable.
     *
     * @return void
     */
    protected $pushNotificationService;

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    /*
    *   Sending message and event to pusher
    *   Kalpesh Joshi
    *   28-July-2022
    */
    /**
     * @OA\Post(
     * path="/api/v1/send-message",
     * operationId="sendMessage",
     * tags={"Message"},
     * summary="send Message",
     * description="send Message",
     * security={ {"bearerAuth": {} }},
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"receiver_id"},
     *              @OA\Property(property="receiver_id", type="integer"),
     *              @OA\Property(property="message", type="string"),
     *               @OA\Property(
     *                  property="files[]",
     *                  type="array",
     *                  @OA\Items(
     *                       type="string",
     *                       format="binary",
     *                  ),
     *               ),
     *           ),
     *       ),
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function sendMessage(Request $request) {
        $response = ['status' => true, 'data' => '', 'errors' => []];

        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            'files.*' => [
                'required',
                'mimes:pdf,jpg,jpeg,png,gif,mp4,mov,wmv,avi,webm,mpeg',
                'max:25000'
            ],
            'receiver_id' => 'required',
            'message' => 'required_without:files',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $loggedUsers  = auth()->user();
        $receiverId =  (int)$request->receiver_id;
        $loginUserId = $loggedUsers->iUserId;

        $data= [
            'sender_id' => $loginUserId,
            'receiver_id' => $receiverId,
        ];

        $currentDateTime = Carbon::now()->timestamp;
        $flag = $loginUserId.$receiverId.$currentDateTime;

        //for setingup eventData
        $eventFiles = [];
        $documentData = [];
        $imageData = [];
        $videoData = [];
        $messageContain = [];

        $hasMessage = FALSE;
        $hasFiles = FALSE;

        if($request->filled('message')){
            $data['message'] = $request->message;
            $data['message_type'] = 'text';
            $data['flag'] = $flag;
            $message = ChatMessage::create($data);
            $data['message_id'] = $message->id;
            $data['is_read'] = $message->is_seen ? true : false;
            $data['date_time'] = $message->created_at->format('Y-m-d H:i:s');
            $messageContain[] ='text';
            $response['data'] = 'message sent successfully';
        }

        $hasImage = $hasVideo = $hasAttachment = FALSE;
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $extension = $file->extension();

                $fileName = $file->getClientOriginalName();
                $storedFile = storeFile('chat-media', $file);
                $storedFilePath = basename($storedFile);
                $tempFile = [];
                $tempImage = [];
                $tempVideo = [];

                $data['message'] = $storedFilePath;
                $data['flag'] = $flag;
                $data['file_name'] = $fileName;
                $filePath = getFilePath('chat-media',$data['message']);

                if($extension == 'pdf'){
                    $hasAttachment = TRUE;
                    $data['message_type'] = 'document';
                    $tempFile = ['name' => $fileName, 'path' => $filePath];
                }elseif($extension == 'mp4'){
                    $hasVideo = TRUE;
                    $data['message_type'] = 'video';
                    $tempVideo =  ['name' => $fileName, 'path' => $filePath];
                }else{
                    $hasImage = TRUE;
                    $data['message_type'] = 'image';
                    $tempImage = ['name'=> $fileName, 'path' => $filePath];
                }

                $message = ChatMessage::create($data);

                $dateTime = $message->created_at->format('Y-m-d H:i:s');

                if(count($tempFile) > 0){
                    $tempFile['message_id'] = $message->id;
                    $tempFile['date_time'] = $dateTime;
                    array_push($eventFiles, $tempFile);
                    $documentData['files'][] = $tempFile;//data['message'];
                }
                if(count($tempImage) > 0){
                    $tempImage['message_id'] = $message->id;
                    $tempImage['date_time'] = $dateTime;
                    array_push($eventFiles, $tempImage);
                    $imageData['files'][] = $tempImage;//$data['message'];
                }
                if(count($tempVideo) > 0){
                    $tempVideo['message_id'] = $message->id;
                    $tempVideo['date_time'] = $dateTime;
                    array_push($eventFiles, $tempVideo);
                    $videoData['files'][] = $tempVideo;//$data['message'];
                }
                $data['is_read'] = $message->is_seen ? true : false;
                $data['date_time'] = $dateTime;
                $messageContain[] = 'file';
                $data['files'] = $eventFiles ? $eventFiles: "";
                $response['data'] = 'message sent successfully';
            }
        }

        //pusher config
        $pusherConfig = config('broadcasting.connections.pusher');
        $options = array(
            'cluster' => $pusherConfig['options']['cluster'],
            'encrypted' => true
        );
        $pusher = new Pusher(
            $pusherConfig['key'],
            $pusherConfig['secret'],
            $pusherConfig['app_id'],
            $options
        );

        //Setting and sending data to messageEvent
        if(in_array('text', $messageContain) && in_array('file', $messageContain)){
            unset($data['message_type']);
            $data['message'] = $request->message;
        }elseif(in_array('file', $messageContain) && !in_array('text', $messageContain)){
            unset($data['message_type']);
            unset($data['message']);
        }else{
            unset($data['message_type']);
        }

        //for all event setting up data`
        if(isset($fileName)){
            $data['file_name'] = $fileName;
            unset($data['file_name']);
        }else{
            $data['files'] = "";
        }

        $messageData = $data;
        $messageData['sender_id'] = $data['sender_id'];
        unset($messageData['sender_id']);
        $messageData['receiver_id'] = $data['receiver_id'];
        unset($messageData['receiver_id']);
        $pusher->trigger('chatChannel-'.$loginUserId, 'App\\Events\\MessageEvent', $messageData);
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\MessageEvent', $messageData);

        // For Document event data
        if(!$documentData){
            $documentData['files'] = '';
        }
        $documentData['sender_id'] = $data['sender_id'];
        $documentData['receiver_id'] = $data['receiver_id'];
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\MessageDocument', $documentData);

        //For Images event data
        if(!$imageData){
            $imageData['files'] = '';
        }
        $imageData['sender_id'] = $data['sender_id'];
        $imageData['receiver_id'] = $data['receiver_id'];
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\MessageImage', $imageData);

        //For Video event data
        if(!$videoData){
            $videoData['files'] = '';
        }
        $videoData['sender_id'] = $data['sender_id'];
        $videoData['receiver_id'] = $data['receiver_id'];
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\MessageVideo', $videoData);

        //For connection event data
        $data['sender_id'] = $data['sender_id'];
        unset($data['sender_id']);
        $data['receiver_id'] = $data['receiver_id'];
        unset($data['receiver_id']);
        $connectionData = $data;
        $connectionData['full_name'] = $loggedUsers->full_name;
        $connectionData['profile_picture_url'] = $loggedUsers->profile_picture_url;
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\MessageConnectionList', $connectionData);

        //Send Count on new message
        $unreadMessageCount = ChatMessage::where('is_seen','0')->where('receiver_id',$receiverId)->distinct()->count('flag');
        $messageNotificationData = [
            'unread_message_count'=> $unreadMessageCount,
        ];
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\MessageNotification', $messageNotificationData);

        //Update chat-list on new message
        $latestMessage = ['id'=>'','content'=>'','docs'=>'null','date_time'=>''];
        $chatListMessageNotificationData = [];
        $senderUserDetails = User::distinct()->where('iUserId',$loginUserId)->select('iUserId','vImage','vFirstName','vLastName')->first()->toArray();
        $lastMessage = ChatMessage::where(function ($query) use ($loginUserId, $receiverId) {
            $query->where('sender_id', '=', $loginUserId)
                ->where('receiver_id', '=', $receiverId);
        })->orWhere(function ($query) use ($loginUserId, $receiverId) {
            $query->where('sender_id', '=', $receiverId)
                ->where('receiver_id', '=', $loginUserId);
        })->latest()->orderBy('id', 'desc')->first();
        if($lastMessage->message_type =='document' || $lastMessage->message_type =='image'){
            $latestMessage['content']= '';
            $latestMessage['docs']= $lastMessage->message;
        }else{
            $latestMessage['content']= $lastMessage->message;;
            $latestMessage['docs']= 'null';
        }
        $latestMessage['id']= $lastMessage->id;
        $latestMessage['is_read']= $lastMessage->is_seen ? true : false;
        $latestMessage['date_time']= $lastMessage->created_at->format('Y-m-d H:i:s');
        $senderUserDetails['latest_message'] = $latestMessage;
        $chatListMessageNotificationData = $senderUserDetails;
        $chatListMessageNotificationData['unread_message_count'] = ChatMessage::where('is_seen','0')->where('receiver_id',$receiverId)->where('sender_id',$loginUserId)->distinct()->count('flag');
        $pusher->trigger('chatChannel-'.$receiverId, 'App\\Events\\ChatListMessageNotification', $chatListMessageNotificationData);

        // Notification commented as it is not in Android side
        // $mobilePushNotification = $this->pushNotificationService->mobilePushNotifications([(string)$receiverId], 'Riser message', 'You have a new message', 'https://www.riserapp.in', $senderUserDetails['latest_message']);

        // New Notification code added
        $receiverUser = User::where('iUserId', $receiverId)->first();
        Notification::send($receiverUser, new ChatNotification("You have a new message from - ".$senderUserDetails['full_name'], $senderUserDetails));

        return response($response);
    }

    /*
    *   Retrive chat between two users
    *   Kalpesh Joshi
    *   28-July-2022
    */
    /**
     * @OA\Get(
     * path="/api/v1/get-recent-chat",
     * operationId="getRecentChat",
     * tags={"Message"},
     * summary="get Recent Chat",
     * description="get Recent Chat",
     * security={ {"bearerAuth": {} }},
     * @OA\Parameter(
     *          name="user_id",
     *          description="user id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function getRecentChat(Request $request)
    {

        $response = ['status' => true, 'data' => '', 'errors' => []];
        
        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            'user_id' => 'required|exists:tbl_users,iUserId',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $user  = auth()->user();
        $loggedUser = $user->iUserId;
        $friendId = $request->user_id;


        $offset = $request->page ? ($request->page * intval($request->per_page)) : 0;
        $chatObj = ChatMessage::selectRaw('GROUP_CONCAT(id) as ids,GROUP_CONCAT(message) as msg,GROUP_CONCAT(message_type) as msg_type, MAX(created_at) as created_at')->where(function ($query) use ($loggedUser, $friendId) {
            $query->where('sender_id', '=', $loggedUser)
                ->where('receiver_id', '=', $friendId);
        })->orWhere(function ($query) use ($loggedUser, $friendId) {
            $query->where('sender_id', '=', $friendId)
                ->where('receiver_id', '=', $loggedUser);
        })->groupBy('flag')->orderBy('created_at', 'desc')->skip($offset)->take(15)->get();

        $chatData = ['id' => '', 'content'=>null,'files'=>[],'source' =>'','sender_id'=>'','receiver_id'=>'', 'is_read' => false];

        $finalData=[];

        foreach ($chatObj as $row) {
            $messagesArray = explode(',',$row['ids']);
            if(sizeof($messagesArray) > 1){
                $chatData['files']=[];
                $chatData['content']=null;

                foreach($messagesArray as $mm){
                    $singleMsg = ChatMessage::find($mm);
                    if($singleMsg->message_type == 'text'){
                        $chatData['content']=$singleMsg->message;
                    }
                    if($singleMsg->message_type == 'document' || $singleMsg->message_type == 'image' || $singleMsg->message_type == 'video'){

                        $temp = ['path'=> getFilePath('chat-media',$singleMsg->message),'name'=>$singleMsg->file_name] ;
                        array_push($chatData['files'],$temp);
                    }
                    $chatData['date_time']=$singleMsg->created_at->format('Y-m-d H:i:s');
                }
                $chatData['id'] = (int)$row['ids'];
                $chatData['source']=($loggedUser == $singleMsg->sender_id) ? 'sender' :'receiver';
                $chatData['sender_id']=$singleMsg->sender_id;
                $chatData['receiver_id']=$singleMsg->receiver_id;
                $chatData['is_read'] = $singleMsg->is_seen ? true : false;

            }else{
                $chatData['files']=[];
                $singleMsg = ChatMessage::find($row['ids']);
                if($singleMsg->message_type == 'text'){
                    $chatData['content']=$singleMsg->message;
                    $chatData['files']=[];
                }else{
                    $chatData['content']=null;
                    $temp = ['path'=> getFilePath('chat-media',$singleMsg->message),'name'=>$singleMsg->file_name] ;
                    array_push($chatData['files'],$temp);
                }
                $chatData['id'] = (int)$row['ids'];
                $chatData['source'] = ($loggedUser == $singleMsg->sender_id) ? 'sender' :'receiver';
                $chatData['sender_id'] = $singleMsg->sender_id;
                $chatData['receiver_id'] = $singleMsg->receiver_id;
                $chatData['is_read'] = $singleMsg->is_seen ? true : false;
                $chatData['date_time'] = $singleMsg->created_at->format('Y-m-d H:i:s');
            }
            array_push($finalData,$chatData);
        }
        $friend = User::find($friendId);

        $chats['is_creator']=$friend->hasRole('creator');
        $chats['full_name']=$friend->full_name;
        $chats['profile_picture_url']=$friend->profile_picture_url;

        $chats['message'] = $finalData;//$chatObj ? new MessageCollection($chatObj) : [];
        $chats['unread_message_count'] = ChatMessage::where('is_seen','0')->where('receiver_id',$loggedUser)->where('sender_id',$friendId)->distinct()->count('flag');
        

        $response['data'] =  $chats;
        return response($response);
    }

    /*
    *   Retrive users with chat
    *   Kalpesh Joshi
    *   28-July-2022
    */
    /**
     * @OA\Get(
     * path="/api/v1/get-recent-chat-list",
     * operationId="getRecentChatList",
     * tags={"Message"},
     * summary="get Recent Chat List",
     * description="get Recent Chat List",
     * security={ {"bearerAuth": {} }},

     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function getRecentChatList(Request $request)
    {
        $response = ['status' => true, 'data' => '', 'errors' => []];

        $loggedUsers  = auth()->user();
        $userId = $loggedUsers->iUserId;

        $senderIds = ChatMessage::where('sender_id' ,$loggedUsers->iUserId)->pluck('receiver_id')->toArray();
        $receiverIds = ChatMessage::where('receiver_id',$loggedUsers->iUserId)->pluck('sender_id')->toArray();

        $lists = User::distinct()->wherein('iUserId',$senderIds)->orWherein('iUserId',$receiverIds)->select('iUserId','vImage','vFirstName','vLastName')->get();
        $latestMessage = ['id'=>'','content'=>'','docs'=>'null','dateTime'=>''];
        $finalData= [];

        foreach($lists as $row){

            $loggedUserId = $loggedUsers->iUserId;
            $rowId = $row->iUserId;
            $lastMessage = ChatMessage::where(function ($query) use ($loggedUserId, $rowId) {
                $query->where('sender_id', '=', $loggedUserId)
                    ->where('receiver_id', '=', $rowId);
            })->orWhere(function ($query) use ($loggedUserId, $rowId) {
                $query->where('sender_id', '=', $rowId)
                    ->where('receiver_id', '=', $loggedUserId);
            })->latest()->orderBy('id', 'desc')->first();

            if($lastMessage && ($lastMessage->message_type =='document' || $lastMessage->message_type =='image')){

                $latestMessage['content']= '';
                $latestMessage['docs']= $lastMessage->message;

            }else{
                $latestMessage['content']= $lastMessage ? $lastMessage->message : NULL;
                $latestMessage['docs']= 'null';

            }
            $latestMessage['id']= $lastMessage ? $lastMessage->id : NULL;
            $latestMessage['is_read']= $lastMessage ? $lastMessage->is_seen ? true : false : NULL;
            $latestMessage['date_time']= $lastMessage ? $lastMessage->created_at->format('Y-m-d H:i:s') : NULL;
            $latestMessage['sender_id']= $lastMessage ? $lastMessage->sender_id : NULL;
            $row['latest_message'] = $latestMessage;
            $row['unread_message_count'] = ChatMessage::where('is_seen','0')->where('receiver_id',$loggedUsers->iUserId)->where('sender_id',$rowId)->distinct()->count('flag');
            $finalData[] =$row;
        }
        usort($finalData, function ($item1, $item2) {
            return $item2['latest_message']['date_time'] <=> $item1['latest_message']['date_time'];
        });
        $response['data'] = $finalData;
        return response($response);

    }

    /*
    *   Setting message isSeen flag
    *   Kalpesh Joshi
    *   28-July-2022
   */
    /**
     * @OA\Post(
     * path="/api/v1/is-read",
     * operationId="isRead",
     * tags={"Message"},
     * summary="isRead Message",
     * description="isRead Message",
     * security={ {"bearerAuth": {} }},
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              required={"message_id"},
     *              @OA\Property(property="message_id", type="integer"),
     *
     *           ),
     *       ),
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function isRead(Request $request){
        
        $response = ['status' => true, 'data' => '', 'errors' => []];

        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            'message_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $user  = auth()->user();        
        $loggedUser = $user->iUserId;

        $message = ChatMessage::find($request->message_id);

        // $flag = ChatMessage::where('id',$request->message_id);

        if($message && $message->value('flag')){
            $message->update(['is_seen' => 1]);
            
            // after isRead send updated count to puser
            $unreadMessageCount=ChatMessage::where('is_seen','0')->where('receiver_id',$loggedUser)->distinct()->count('flag');
            $messageNotificationData = [
                'unread_message_count'=> $unreadMessageCount,
            ];
            //pusher config
            $pusherConfig = config('broadcasting.connections.pusher');
            $options = array(
                'cluster' => $pusherConfig['options']['cluster'],
                'encrypted' => true
            );
            $pusher = new Pusher(
                $pusherConfig['key'],
                $pusherConfig['secret'],
                $pusherConfig['app_id'],
                $options
            );
            $pusher->trigger('chatChannel-'.$message->sender_id, 'App\\Events\\MessageIsRead', $message);
            $pusher->trigger('chatChannel-'.$message->receiver_id, 'App\\Events\\MessageNotification', $messageNotificationData);
        }else{
            $response['status'] = false;
            $response['errors'] = 'No message found';
        }
        return response($response);
    }
}
