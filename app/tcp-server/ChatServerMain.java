public class ChatServerMain {
    public static void main(String[] args) {
        int port = 20205;
        // 서버 객체 생성 후 시작
        ChatServer chatServer = new ChatServer(port);
        chatServer.start();
    }
}
