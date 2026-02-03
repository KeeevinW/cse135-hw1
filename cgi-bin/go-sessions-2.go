package main

import (
	"fmt"
	"io/ioutil"
	"math/rand"
	"net/url"
	"os"
	"strings"
	"time"
)

func main() {
	// Read Post Body
	inputData, _ := ioutil.ReadAll(os.Stdin)
	params, _ := url.ParseQuery(string(inputData))
	
	username := params.Get("username")
	destroy := params.Get("destroy")
	
	// Read Cookie
	cookieHeader := os.Getenv("HTTP_COOKIE")
	sessionID := ""
	
	if strings.Contains(cookieHeader, "GOSESSID=") {
		parts := strings.Split(cookieHeader, "; ")
		for _, part := range parts {
			if strings.HasPrefix(part, "GOSESSID=") {
				sessionID = strings.TrimPrefix(part, "GOSESSID=")
			}
		}
	}

	// Handle Destroy
	if destroy == "true" {
		if sessionID != "" {
			os.Remove("/tmp/gosess_" + sessionID)
		}
		fmt.Println("Set-Cookie: GOSESSID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/")
		fmt.Println("Content-Type: text/html\n")
		fmt.Println("Session destroyed. <a href='/cgi-bin/go-sessions-1.cgi'>Back</a>")
		return
	}

	// Handle New Login
	if username != "" {
		// Create new Session ID
		rand.Seed(time.Now().UnixNano())
		sessionID = fmt.Sprintf("%d", rand.Int63())
		
		// Write to server file
		err := ioutil.WriteFile("/tmp/gosess_"+sessionID, []byte(username), 0644)
		if err == nil {
			fmt.Printf("Set-Cookie: GOSESSID=%s; path=/\n", sessionID)
		}
	}

	// Read Data
	storedName := "No Session Found"
	if sessionID != "" {
		data, err := ioutil.ReadFile("/tmp/gosess_" + sessionID)
		if err == nil {
			storedName = string(data)
		}
	}

	fmt.Println("Content-Type: text/html\n")
	fmt.Printf(`
    <html>
    <body style="font-family: sans-serif; padding: 2rem;">
        <h1>Go Session Page 2</h1>
        <p>Name from Server File: <strong>%s</strong></p>
        <p>Session ID: %s</p>
        
        <form method="POST" action="/cgi-bin/go-sessions-2.cgi">
            <input type="hidden" name="destroy" value="true">
            <button type="submit">Destroy Session</button>
        </form>
        <br>
        <a href="/cgi-bin/go-sessions-1.cgi">Back to Page 1</a>
    </body>
    </html>
    `, storedName, sessionID)
}