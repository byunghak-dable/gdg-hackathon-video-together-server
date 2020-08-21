import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.PrintWriter;
import java.net.Socket;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.List;
import java.util.TimeZone;

public class ChatUser extends Thread {

    // 클라이언트 소켓 생성
    private Socket clientSocket;
    private RoomManager roomManager;

    // 기본적인 유저 info
    // TODO: 커플 id 사용않할 수 있음
    private int userId;
    private String userName;
    private String profileImageUrl;

    private int roomId;
    private int youtubeRoomId;

    PrintWriter printWriter;
    BufferedReader bufferedReader;
    DateFormat dateFormat;

    // 채팅 룸
    private ChatRoom chatRoom;
    private ChatRoom youtubeRoom;

    public ChatUser(Socket clientSocket, RoomManager roomManager) {
        this.clientSocket = clientSocket;
        this.roomManager = roomManager;
    }

    @Override
    public void run() {
        super.run();
        try {
            System.out.println("스레드 갯수 : " + Thread.activeCount());
            handleClientSocket();
            System.out.println("스레드 갯수 : " + Thread.activeCount());

        } catch (IOException e) {
            e.printStackTrace();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }

    // 클라이언트 소켓의 요청을 받고 그에 맞는 결과를 반환해주는 메소드 이다.
    private void handleClientSocket() throws IOException, InterruptedException {
        InputStream clientInPutStream = clientSocket.getInputStream();
        OutputStream clientOutPutStream = clientSocket.getOutputStream();

        printWriter = new PrintWriter(clientOutPutStream);
        bufferedReader = new BufferedReader(new InputStreamReader(clientInPutStream));
        dateFormat = new SimpleDateFormat("hh:mm a");
        dateFormat.setTimeZone(TimeZone.getTimeZone("Asia/Seoul")); // 한국 시간으로 설정

        String messageLine;

        // 소켓이 연결을 끊기 전까지는 지속적으로
        while ((messageLine = bufferedReader.readLine()) != null) {

            // quit이 들어오면 리스트에서 socketThread 제거
            if ("quit".equalsIgnoreCase(messageLine)) {
                break;

            } else {
                /*
                 * 종료가 아니면 문자열 split <1> socket 변수 지정할 경우 (socket 처음 접속 시) 0:
                 * registerSocket(명령어) - 1: 커플 ID - 2 : 나의 ID(유저) 커플 ID - 나의 ID - 이름 - 이미지 <2>
                 * 메시지 보낼 시 커플 ID - 나의 ID - 이름 - 이미지 url - message
                 */
                String[] splitedData = formSplitData(messageLine);
                switch (splitedData[0]) {

                    // 처음 소켓과 연결하면 해당 유저 정보를 ChatUser 전역 변수에 저장한다.
                    case "registerUserInfo":
                        registerSocketInfo(splitedData);
                        break;

                    // ----------- 채팅 룸 관련 명령어 -----------
                    case "joinChatRoom":
                        int roomId = Integer.parseInt(splitedData[1]);
                        this.setRoomId(roomId);
                        System.out.println("방 id :" + this.getRoomId());
                        this.setChatRoom(
                                roomManager.joinChatRoom(roomManager.getChatRoomList(), this.getRoomId(), this));
                        break;

                    case "exitChatRoom":
                        roomManager.exitChatRoom(roomManager.getChatRoomList(), chatRoom, this);
                        break;

                    case "sendChatMessage":
                        // 커플 방에 존재하는 모든 유저에게 메시지를 보낸다.
                        for (ChatUser chatUser : chatRoom.getUserList()) {
                            chatUser.send(formChatMessage("chat", splitedData));
                        }
                        break;

                    // ----------- 유투브 룸 관련 명령어 -----------
                    case "createYoutubeRoom":
                        int youtubeRoomId = Integer.parseInt(splitedData[1]);
                        this.setYoutuebRoomId(youtubeRoomId);
                        this.setYoutubeRoom(roomManager.createYoutubeRoom(roomManager.getYoutubeRoomList(),
                                this.getRoomId(), this));

                        System.out.println("유투브 방 갯수 :" + roomManager.getYoutubeRoomList().size());
                        System.out.println("츄투브 방 id :" + this.getYoutubeRoomId());
                        break;

                    case "joinYoutubeRoom":
                        int invitedRoomId = Integer.parseInt(splitedData[1]);

                        joinYoutubeRoom(invitedRoomId, splitedData);
                        break;

                    case "syncYoutubePlayer":
                        ChatUser firstUser = youtubeRoom.getUserList().get(0);
                        String flag = "youtubeJoinRoom";
                        String firstUserMessage = flag + "@visitorJoin@" + userId + "@0";

                        firstUser.send(firstUserMessage);
                        System.out.println("syncYoutubePlayer : " + firstUserMessage);
                        break;

                    case "sendPlayerStateToVisitor":
                        int visitorUserId = Integer.parseInt(splitedData[3]);

                        for (ChatUser chatUser : youtubeRoom.getUserList()) {
                            if (chatUser.getUserId() == visitorUserId) {
                                chatUser.send(formYoutubeMessage(splitedData));
                                break;
                            }
                        }
                        break;

                    case "exitYoutubeRoom":
                        boolean isRoomDestroyed = roomManager.exitChatRoom(roomManager.getYoutubeRoomList(),
                                youtubeRoom, this);

                        if (!isRoomDestroyed) {
                            sendYoutubeParticipants();
                        }
                        break;

                    case "sendYoutubePlayerState":
                        for (ChatUser chatUser : youtubeRoom.getUserList()) {
                            if (chatUser != this) {
                                chatUser.send(formYoutubeMessage(splitedData));
                            }
                        }
                        break;

                    case "sendYoutubeChat":
                        // 커플 방에 존재하는 모든 유저에게 메시지를 보낸다.
                        System.out.println(splitedData[1]);
                        System.out.println(youtubeRoom.getUserList().size());

                        for (ChatUser chatUser : youtubeRoom.getUserList()) {
                            chatUser.send(formChatMessage("youtubeChat", splitedData));
                        }
                        break;
                }
            }
        }
        disConnectMultiRoom();
        clientSocket.close();
        System.out.println("클라이언트 소켓 닫힘!!");
    }

    // ----------- 채팅 메시지를 관리하거나 클래스의 변수 선언과 관련된 메소드 모음 -----------

    // 서버로 split을 사용한 상태로 데이터를 전달 -> 따라서 개별 String 을 사용하기 위해 변환
    private String[] formSplitData(String messageLine) {
        String delimiter = "@";
        return messageLine.split(delimiter);
    }

    /*
     * 받아온 데이터(String) => 0: registerSocket(명령어) - 1: 커플 ID - 2 : 나의 ID(유저) - 3 : 유저
     * 이름 - 4 : 프로필 이미지 url 전역 변수로 저장 메시지를 보낼 때 소켓의 정보로 사용.
     */
    private void registerSocketInfo(String[] splitedData) {
        this.userId = Integer.parseInt(splitedData[1]);
        this.userName = splitedData[2];
        this.profileImageUrl = splitedData[3];
        System.out.println("소켓 등록 됨 (등록한 유저 Id : " + this.userId);
    }

    private void joinYoutubeRoom(int roomId, String[] splitedData) throws IOException {
        ChatRoom joinedRoom = roomManager.joinYoutubeRoom(roomManager.getYoutubeRoomList(), this.getRoomId(), this);
        String flag = "youtubeJoinRoom";

        if (joinedRoom == null) {
            String message = flag + "@empty@" + userId + "@0";

            this.send(message);
        } else {
            this.setYoutubeRoom(joinedRoom);
            sendYoutubeParticipants();
        }
    }

    private void sendYoutubeParticipants() throws IOException {
        String flag = "youtubeJoinRoom";

        int participantCount = youtubeRoom.getUserList().size();
        String participantsMessage = flag + "@participantCount@" + userId + "@" + participantCount;

        for (ChatUser chatUser : youtubeRoom.getUserList()) {
            chatUser.send(participantsMessage);
        }
        System.out.println("참여인원 : " + participantsMessage);
    }

    // 메시지를 만드는 메소드로 현재 메시지를 보내는 소켓의 정보와 사용자가 보낸 메시지를 결합하여 다른 소켓들에게 전달
    private String formChatMessage(String flag, String[] splitedData) {
        String delimeter = "@";
        String roomId = splitedData[1];
        String message = splitedData[2];
        StringBuilder messageData = new StringBuilder();
        messageData.append(flag).append(delimeter).append(roomId).append(delimeter).append(this.userId)
                .append(delimeter).append(this.userName).append(delimeter).append(this.profileImageUrl)
                .append(delimeter).append(message).append(delimeter)
                .append(dateFormat.format(System.currentTimeMillis()));

        return messageData.toString();
    }

    private String formYoutubeMessage(String[] splitedData) {
        String flag = "youtubeState";
        String delimeter = "@";
        String playerState = splitedData[1];
        String currentTime = splitedData[2];
        StringBuilder messageData = new StringBuilder();

        messageData.append(flag).append(delimeter).append(playerState).append(delimeter).append(currentTime);
        return messageData.toString();
    }

    // 메시지를 보내는 메소드
    private void send(String messageData) throws IOException {
        printWriter.println(messageData);
        printWriter.flush();
    }

    // private void disconnect() throws IOException {
    // this.clientSocket.close();
    // this.bufferedReader.close();
    // System.out.println("클라이언트가 호스트에 대한 연결을 끊음 ");
    // }

    // socket 접속을 끊을 때
    private void disConnectMultiRoom() throws IOException {
        // -- 소켓 연결이 끊겼을 때 소켓이 참여한 모든 오픈 채팅방에서 소켓 제거하는 로직 --
        clearUserFromRoom(roomManager.getChatRoomList());
        clearUserFromRoom(roomManager.getYoutubeRoomList());
        // 채팅방에서 chatUser 제거

        this.clientSocket.close();
        this.bufferedReader.close();
        System.out.println("클라이언트가 호스트에 대한 연결을 끊음 ");
    }

    private void clearUserFromRoom(ArrayList<ChatRoom> roomList) {
        int roomCount = roomList.size();

        for (int i = 0; i < roomCount; i++) {
            ChatRoom room = roomList.get(i);
            List<ChatUser> chatUserList = room.getUserList();
            int chatUserCount = chatUserList.size();

            for (int j = 0; j < chatUserCount; j++) {
                ChatUser chatUser = chatUserList.get(j);
                if (chatUser.getUserId() == getUserId()) {
                    boolean isRoomDestroyed = roomManager.exitChatRoom(roomList, room, this);
                    System.out.println(roomCount);

                    // 채팅방에 아무도 없어 방자체가 소멸되었다면, 채팅방 전체 갯수 감소시키기
                    if (isRoomDestroyed) {
                        roomCount--;
                        i--;
                    }
                    break;
                }
            }
        }
    }

    // ----------- 채팅 방과 관련된 메소드 모음 -----------

    // 유저가 채팅방에 들어갔을 때 호출하는 메소드
    public void joinChatRoom(ChatRoom chatRoom) {
        this.chatRoom = chatRoom;

        // TODO: 테스트 용 지우기
        for (ChatUser user : chatRoom.getUserList()) {

            System.out.println(user.getId());
        }
    }

    // 유저가 채팅방에 나갔을 때 호출하는 메소드
    public void exitChatRoom(ChatRoom chatRoom) {
        // this.setOpenChatRoom(null);
    }

    // ----------- Getter Setter -----------

    public Socket getClientSocket() {
        return clientSocket;
    }

    public void setClientSocket(Socket clientSocket) {
        this.clientSocket = clientSocket;
    }

    public int getRoomId() {
        return roomId;
    }

    public void setRoomId(int roomId) {
        this.roomId = roomId;
    }

    public int getYoutubeRoomId() {
        return youtubeRoomId;
    }

    public void setYoutuebRoomId(int youtubeRoomId) {
        this.youtubeRoomId = youtubeRoomId;
    }

    public int getUserId() {
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
    }

    public ChatRoom getChatRoom() {
        return chatRoom;
    }

    public void setChatRoom(ChatRoom chatRoom) {
        this.chatRoom = chatRoom;
    }

    public ChatRoom getYoutubeRoom() {
        return youtubeRoom;
    }

    public void setYoutubeRoom(ChatRoom youtubeRoom) {
        this.youtubeRoom = youtubeRoom;
    }
}