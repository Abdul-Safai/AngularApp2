// register.component.ts
import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent {
  username = '';
  email = '';
  password = '';
  errorMessage = '';
  successMessage = '';

  constructor(private http: HttpClient, private router: Router) {}

  register(): void {
    this.errorMessage = '';
    this.successMessage = '';

    const user = {
      username: this.username,
      email: this.email,
      password: this.password
    };

    this.http.post<any>('http://localhost/AngularApp2/angularapp_api/register_user.php', user).subscribe({
      next: (response) => {
        if (response.message) {
          this.successMessage = response.message;
          setTimeout(() => this.router.navigate(['/login']), 2000); // Redirect after success
        } else {
          this.errorMessage = response.error || 'Registration failed.';
        }
      },
      error: (error) => {
        this.errorMessage = 'Something went wrong. Please try again.';
        console.error('Registration error:', error);
      }
    });
  }
}
