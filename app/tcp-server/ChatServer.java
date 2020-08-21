
import java.io.IOException;
import java.net.ServerSocket;
import java.net.Socket;

// 서버 클래스 -> 클라이언트의 소켓을 관리
public class ChatServer extends Thread {

    private ServerSocket serverSocket;
    // 서버포트 20205
    private final int serverPort;

    // 생성자 포트를 입력 받아 생성
    public ChatServer(int serverPort) {
        this.serverPort = serverPort;
    }

    @Override
    public void run() {
        try {
            serverSocket = new ServerSocket(serverPort);
            RoomManager roomManager = new RoomManager();
            while (true) {
                System.out.println("About to accept client connection...");
                Socket clientSocket = serverSocket.accept();
                System.out.println("Accept client connection from : " + clientSocket);
                ChatUser chatUser = new ChatUser(clientSocket, roomManager);
                chatUser.start();
            }
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            try {
                serverSocket.close();
            } catch (IOException e) {
                e.printStackTrace();
                System.out.println("서버 소켓 closing error");
            }
        }
    }
}
