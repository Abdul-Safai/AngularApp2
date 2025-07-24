import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  standalone: true,
  selector: 'app-welcome',
  imports: [CommonModule, RouterModule],
  templateUrl: './welcome.html',
  styleUrls: ['./welcome.css']
})
export class WelcomeComponent {}
