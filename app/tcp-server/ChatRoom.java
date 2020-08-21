import java.util.ArrayList;
import java.util.List;

public class ChatRoom {

    private int roomID;
    private List<ChatUser> userList;

    // 아무도 없는 방을 생성할 경우
    public ChatRoom(int roomID) {
        this.setRoomId(roomID);
        userList = new ArrayList<ChatUser>();
    }

    // 새로운 유저가 채팅방에 들어온 경우
    public void userJoin(ChatUser chatUser) {
        System.out.println("채팅방에 참여했습니다.");
        userList.add(chatUser);
        chatUser.joinChatRoom(this);

        for (ChatUser user : userList) {
            System.out.println(user.getUserId());
        }
        System.out.println("ChatRoom.userJoin(): 유저가 채팅 방에 참여함");
    }

    // 유저가 방을 나가는 경우
    public void userExit(ChatUser chatUser) {
        chatUser.exitChatRoom(this);
        userList.remove(chatUser);
        System.out.println("ChatRoom.userExit(): 유저가 방을 나감");
    }

    // 룸을 삭제
    public void closeRoom() {
        for (ChatUser user : userList) {
            user.exitChatRoom(this);
        }

        this.userList.clear();
        this.userList = null;
        System.out.println("ChatRoom.close(): 룸 삭제됨");
    }

    // ----------- Getter Setter -----------

    public int getRoomId() {
        return roomID;
    }

    public void setRoomId(int roomID) {
        this.roomID = roomID;
    }

    public List<ChatUser> getUserList() {
        return userList;
    }

    public void setUserList(List<ChatUser> userList) {
        this.userList = userList;
    }
}