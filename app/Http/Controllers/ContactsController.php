<?php

namespace App\Http\Controllers;

use App\User;
use App\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;

use \Auth;

class ContactsController extends Controller
{
    public function get()
    {
      $peopleUserMessaged = Message::where('from', '=', auth()->id())->get()->pluck('to')->toArray();
      $peopleThatSentMessage = Message::where('to', '=', auth()->id())->get()->pluck('from')->toArray();
      $listOfContacts = array_values(array_unique(array_merge($peopleUserMessaged, $peopleThatSentMessage)));

      $contacts = User::whereIn('id', $listOfContacts)->get();

      $unreadIds = Message::select(\DB::raw('`from` as sender_id, count(`from`) as messages_count'))
        ->where('to', auth()->id())
        ->where('read', false)
        ->groupBy('from')
        ->get();

      $contacts = $contacts->map(function($contact) use ($unreadIds) {
        $contactUnread = $unreadIds->where('sender_id', $contact->id)->first();
        $contact->unread = $contactUnread ? $contactUnread->messages_count : 0;
        return $contact;
      });
      return response()->json($contacts);
    }

    public function getMessagesFor($id){
      $messages = Message::betweenTwo($id)->orderBy('created_at','asc')->get();
      return response()->json($messages);
    }

    public function send(Request $request){
      $message = Message::create([
        'from' => auth()->id(),
        'to' => $request->contact_id,
        'text' => $request->text
      ]);

      broadcast(new NewMessage($message));
      return response()->json($message);
    }

    public function messageRead(Request $request, $id){
      Message::where('from', $request->contact_id)
        ->where('to', auth()->id())
        ->update(['read' => 1]);
    }
}
