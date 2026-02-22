package main

import (
	"encoding/json"
	"fmt"
	"os"
	"time"
)

func main() {
	fmt.Print("Content-Type: application/json\n\n")

	userIP := os.Getenv("REMOTE_ADDR")
	if userIP == "" {
		userIP = "Unknown"
	}

	data := map[string]string{
		"message":    "Greetings from Xuanye!",
		"language":   "Go",
		"ip_address": userIP,
		"time":       time.Now().Format(time.RFC3339),
	}

	jsonOutput, _ := json.Marshal(data)
	fmt.Println(string(jsonOutput))
}