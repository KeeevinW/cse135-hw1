package main

import (
	"fmt"
	"io/ioutil"
	"net/http"
	"net/http/cgi"
)

func main() {
	cgi.Serve(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Cache-Control", "no-cache")
		w.Header().Set("Content-Type", "text/html")

		// Read Body
		bodyBytes, _ := ioutil.ReadAll(r.Body)
		bodyString := string(bodyBytes)

		fmt.Fprintf(w, `<!DOCTYPE html>
<html><head><title>General Request Echo</title>
</head><body><h1 align="center">General Request Echo</h1>
<hr>
<p><b>HTTP Protocol:</b> %s</p>
<p><b>HTTP Method:</b> %s</p>
<p><b>Query String:</b> %s</p>
<p><b>Message Body:</b> %s</p>
</body></html>`, r.Proto, r.Method, r.URL.RawQuery, bodyString)
	}))
}