#include <iostream>
#include <string>
#include <vector>
#include <fstream>
#include <windows.h>
#include <winsock2.h>
#include <stdio.h>
#include <stdlib.h>

#include <iphlpapi.h>

#define _WIN32_WINNT 0x501
#define DEFAULT_PORT "27015"

#include <ws2tcpip.h>

/*
This is based on the following example:
https://msdn.microsoft.com/en-us/library/windows/desktop/ms737525(v=vs.85).aspx
*/

using namespace std;

 class business {
    public:
        unsigned long firstVal;
        short nextVal;

 	business () {
        cout << "make a business";
 	}
 };

 class factory {
     public:
        int firstVal, nextVal;

    factory (int x, int y) {
        firstVal = x;
        cout << "make a factory with the value of " << firstVal;
    }
 };

int main () {
    business testBusiness;
    factory testFactory(1,2);

    int counter;
    counter =0;
    while (counter < 1000) {
        counter++;
    }

    cout << " Final count is " << counter;
   WSADATA wsaData;

    // Initialize Winsock
    int iResult, iSendResult;
    fd_set fd;
    timeval timeVal;
    unsigned long nbio;
    iResult = WSAStartup(MAKEWORD(2,2), &wsaData);
    if (iResult != 0) {
        printf("WSAStartup failed: %d\n", iResult);
        return 1;
    } else {
        cout << "startup successful: " << iResult;
    }

    struct addrinfo *result = NULL, *ptr = NULL, hints;

    ZeroMemory(&hints, sizeof (hints));
    hints.ai_family = AF_INET;
    hints.ai_socktype = SOCK_STREAM;
    hints.ai_protocol = IPPROTO_TCP;
    hints.ai_flags = AI_PASSIVE;

    cout << "\nADDRESS INFO: " << sizeof(hints.ai_addr) << " -> " << hints.ai_addrlen << "gives ";
    cout << hints.ai_addr;

    // Resolve the local address and port to be used by the server
    iResult = getaddrinfo(NULL, DEFAULT_PORT, &hints, &result);
    if (iResult != 0) {
        printf("getaddrinfo failed: %d\n", iResult);
        WSACleanup();
        return 1;
    }

    SOCKET ListenSocket = INVALID_SOCKET;

    cout << "\nResult socket info: " << result->ai_family << ", " << result->ai_socktype << ", " << result->ai_protocol;
    // Create a SOCKET for the server to listen for client connections
    ListenSocket = socket(result->ai_family, result->ai_socktype, result->ai_protocol);

    if (ListenSocket == INVALID_SOCKET) {
    printf("Error at socket(): %ld\n", WSAGetLastError());
    freeaddrinfo(result);
    WSACleanup();
    return 1;
    }

    // Setup the TCP listening socket
    cout << "\nBIND: " << result->ai_addr << ", " << result->ai_addrlen;
    iResult = bind( ListenSocket, result->ai_addr, (int)result->ai_addrlen);
    if (iResult == SOCKET_ERROR) {
        printf("bind failed with error: %d\n", WSAGetLastError());
        freeaddrinfo(result);
        closesocket(ListenSocket);
        WSACleanup();
        return 1;
    }
    freeaddrinfo(result);

    cout << "\nLISTENING!";
    if ( listen( ListenSocket, SOMAXCONN ) == SOCKET_ERROR ) {
    printf( "Listen failed with error: %ld\n", WSAGetLastError() );
    closesocket(ListenSocket);
    WSACleanup();
    return 1;
    }
    sockaddr_in sin;
    socklen_t len = sizeof(sin);
    if (getsockname(ListenSocket, (struct sockaddr *)&sin, &len) == -1) {
            //perror("getockname");
            printf("getsockname failed with error: %d\n", WSAGetLastError());
    } else printf("port number %d\n", ntohs(sin.sin_port));

    char charTest [4];
    itoa(sin.sin_addr.S_un, charTest,10);
    cout << "\nSIZE: " << sizeof(sin.sin_addr.S_un) << ", " << charTest[0];

    cout << "\nACCEPTING A CONNECTION";
    // ACCEPTING A CONNECTION
    SOCKET ClientSocket;
    ClientSocket = INVALID_SOCKET;

    nbio = 0;
    ioctlsocket(ListenSocket, FIONBIO, &nbio);
    ioctlsocket(ClientSocket, FIONBIO, &nbio);

    // Accept a client socket
    FD_ZERO(&fd);
    FD_SET(ListenSocket, &fd);

    timeVal.tv_sec = 5;
    timeVal.tv_usec = 5;
    if (select(0, &fd, NULL, NULL, &timeVal) > 0) {
        cout << ClientSocket;
        ClientSocket = accept(ListenSocket, NULL, NULL);
        cout << ClientSocket;
        if (ClientSocket == INVALID_SOCKET) {
            printf("accept failed: %d\n", WSAGetLastError());
            closesocket(ListenSocket);
            WSACleanup();
            return 1;
        }

        cout << "\nSEND AND RECEIVE INFO";
        // RECEIVING AND SENDING DATA ON THE SERVER
        #define DEFAULT_BUFLEN 512

        char recvbuf[DEFAULT_BUFLEN];
        int recvbuflen = DEFAULT_BUFLEN;
        // Receive until the peer shuts down the connection
        do {
            iResult = recv(ClientSocket, recvbuf, recvbuflen, 0);
            if (iResult > 0) {
                printf("Bytes received: %d\n", iResult);

                // Echo the buffer back to the sender
                iSendResult = send(ClientSocket, recvbuf, iResult, 0);
                if (iSendResult == SOCKET_ERROR) {
                    printf("send failed: %d\n", WSAGetLastError());
                    closesocket(ClientSocket);
                    WSACleanup();
                    return 1;
                }
                printf("Bytes sent: %d\n", iSendResult);
            } else if (iResult == 0)
                printf("Connection closing...\n");
            else {
                printf("recv failed: %d\n", WSAGetLastError());
                closesocket(ClientSocket);
                WSACleanup();
                return 1;
            }
        cout << "IRESULT: " << iResult;
        } while (iResult > 0);

        // DISCONNECT AND SHUT DOWN
        // shutdown the send half of the connection since no more data will be sent
        iResult = shutdown(ClientSocket, SD_SEND);
        if (iResult == SOCKET_ERROR) {
            printf("shutdown failed: %d\n", WSAGetLastError());
            closesocket(ClientSocket);
            WSACleanup();
            return 1;
        }

        // cleanup
        closesocket(ClientSocket);
        WSACleanup();

        return 0;
    }
}
