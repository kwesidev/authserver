package utilities

import (
	"crypto/sha1"
	"fmt"
	"math/rand"
	"os"
	"time"

	"github.com/golang-jwt/jwt/v5"
)

type authClaim struct {
	UserId int      `json:"userId"`
	Roles  []string `json:"roles"`
	jwt.RegisteredClaims
}

var jwtSecret = []byte(os.Getenv("JWT_SECRET"))

// Generates a Jwt Token return a string or error
func GenerateJwtToken(userId int, roles []string, expiry time.Duration) (string, error) {
	claims := authClaim{
		userId,
		roles,
		jwt.RegisteredClaims{
			ExpiresAt: jwt.NewNumericDate(time.Now().Add(expiry)),
		},
	}
	claimToken := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	return claimToken.SignedString(jwtSecret)
}

// ValidatesJWtAndGetClaims the JWT Key and return the claims
func ValidateJwtAndGetClaims(tokenString string) (map[string]interface{}, error) {
	res := make(map[string]interface{})
	token, err := jwt.ParseWithClaims(tokenString, &authClaim{}, func(token *jwt.Token) (interface{}, error) {
		return jwtSecret, nil
	})
	if claims, ok := token.Claims.(*authClaim); ok && token.Valid {
		res["userId"] = claims.UserId
		res["roles"] = claims.Roles
		return res, nil
	} else {
		return nil, err
	}
}

// GenerateOpaqueToken function to generate random tokens
func GenerateOpaqueToken(length int) string {
	var alphaNum = []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")
	randomLetters := make([]rune, length)
	// Generate Sudo random
	rand.Seed(time.Now().UnixNano())
	for i := range randomLetters {
		randomLetters[i] = alphaNum[rand.Intn(len(randomLetters))]
	}
	// Convert to sha1 string
	randomString := string(randomLetters)
	return fmt.Sprintf("%x", sha1.Sum([]byte(randomString)))
}
