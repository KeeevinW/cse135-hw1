package main

import (
	"encoding/json"
	"fmt"
	"time"
)

func main() {
	fmt.Print("Content-Type: application/json\n\n")

	data := map[string]string{
		"message":  "Hello Team Xuanye",
		"language": "Go",
		"time":     time.Now().String(),
	}

	jsonOutput, _ := json.Marshal(data)
	fmt.Println(string(jsonOutput))
}