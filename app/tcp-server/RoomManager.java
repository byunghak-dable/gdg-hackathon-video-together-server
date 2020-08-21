import java.util.ArrayList;
import java.util.List;

public class RoomManager {
    private ArrayList<ChatRoom> chatRoomList = new ArrayList<ChatRoom>();
    private ArrayList<ChatRoom> youtubeRoomList = new ArrayList<ChatRoom>();

    // - 채팅 방의 RoomID -
    // 1. ChatRoom : 고유의 ID를 사용
    // 2. OpenChatRoom : open_chat_room 테이블의 id 값을 사용

    // - 채팅 방 생성 및 참여 -
    // 채팅방을 생성하거나 참여 모두 join~ 메소드를 사용하며, 사용자가 채팅에 참여하면 채팅방이 존재 유무에 따라 생성하거나 참여
    public ChatRoom joinChatRoom(ArrayList<ChatRoom> roomList, int ID, ChatUser chatUser) {
        // 이미 ID 로 커플 채팅방이 생성 되어 있다면, 생성 되어있는 커플 채팅방 반환
        for (ChatRoom ChatRoom : roomList) {
            if (ChatRoom.getRoomId() == ID) {
                ChatRoom.userJoin(chatUser);
                System.out.println("기존에 채팅방이 있어 기존 방에 유저 참여");
                return ChatRoom;
            }
        }
        // 생성된 채팅방이 없다면 채팅방을 생성한다
        ChatRoom ChatRoom = new ChatRoom(ID);
        roomList.add(ChatRoom);
        ChatRoom.userJoin(chatUser);
        System.out.println("새로운 방에 유저 참여");
        return ChatRoom;
    }

    public ChatRoom createYoutubeRoom(ArrayList<ChatRoom> roomList, int ID, ChatUser chatUser) {
        // 생성된 채팅방이 없다면 채팅방을 생성한다
        ChatRoom ChatRoom = new ChatRoom(ID);
        roomList.add(ChatRoom);
        ChatRoom.userJoin(chatUser);
        System.out.println("새로운 방에 유저 참여");
        return ChatRoom;
    }

    public ChatRoom joinYoutubeRoom(ArrayList<ChatRoom> roomList, int ID, ChatUser chatUser) {
        // 이미 ID 로 커플 채팅방이 생성 되어 있다면, 생성 되어있는 커플 채팅방 반환
        for (ChatRoom ChatRoom : roomList) {
            if (ChatRoom.getRoomId() == ID) {
                ChatRoom.userJoin(chatUser);
                System.out.println("기존에 채팅방이 있어 기존 방에 유저 참여");
                return ChatRoom;
            }
        }
        return null;
    }

    /*
     * 사용자가 룸에서 빠져나가는 메소드 - 룸에 유저 리스트에서 사용자가 나가는 것을 알린다 ->
     * chatRoom.userExit(chatUser); - 만약 룸의 유저 리스트 요소가 1 보다 작으면 룸도 삭제
     */
    public boolean exitChatRoom(ArrayList<ChatRoom> roomList, ChatRoom chatRoom, ChatUser chatUser) {
        chatRoom.userExit(chatUser);

        if (chatRoom.getUserList().size() < 1) {
            removeChatRoom(roomList, chatRoom);
            System.out.println("RoomManager.exitUserFromRoom() : 채팅 룸에 사용자가 없어 룸을 삭제함");
            return true;
        }
        System.out.println("RoomManager.exitUserFromRoom() : 사용자가 방을 나감");
        return false;
    }

    // 룸을 삭제하는 메소드 : exitUserFromRoom() 메소드에서 조건에 맞으면 호출한다.
    private void removeChatRoom(ArrayList<ChatRoom> roomList, ChatRoom chatRoom) {

        chatRoom.closeRoom();
        roomList.remove(chatRoom);
        System.out.println("RoomManager.removeRoom() : 채팅 룸 삭제 됨");
        System.out.println(getChatRoomCount());
    }

    /*
     * TODO : 방의 정보를 리스트화 시켜서 반환하는 메소드로 변경 해야할 거 같다. -> 사용자가 안드로이드에서 어떤 방이 생성되어있는지 볼
     * 수 있게
     */

    public int getChatRoomCount() {
        return chatRoomList.size();
    }

    public ArrayList<ChatRoom> getChatRoomList() {
        return this.chatRoomList;
    }

    public ArrayList<ChatRoom> getYoutubeRoomList() {
        return this.youtubeRoomList;
    }
}