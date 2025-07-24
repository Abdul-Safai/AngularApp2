import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  email = '';
  password = '';
  errorMessage = '';

  constructor(private http: HttpClient, private router: Router) {}

  login(): void {
    this.errorMessage = '';

    this.http.post<any>('http://localhost/AngularApp2/angularapp_api/login_user.php', {
      email: this.email,
      password: this.password
    }).subscribe({
      next: (res) => {
        console.log('Login response ->', res);
        const ok = res?.success === true || res?.message === 'Login successful';
        if (ok) {
          // store user if you want
          localStorage.setItem('user', JSON.stringify(res.user ?? {}));
          this.router.navigate(['/home']);   // <- use a route that exists
        } else {
          this.errorMessage = res?.error || 'Login failed. Please try again.';
        }
      },
      error: (err) => {
        console.error('Login error ->', err);
        this.errorMessage = err?.error?.error || 'Invalid email or password.';
      }
    });
  }
}
