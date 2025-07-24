import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private loginUrl = 'http://localhost/AngularApp2/angularapp_api/login_user.php';

  constructor(private http: HttpClient) {}

  login(email: string, password: string) {
    return this.http.post(this.loginUrl, { email, password });
  }
}
